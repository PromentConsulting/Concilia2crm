<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class MauticService
{
    public function getSettings(): array
    {
        $row = IntegrationSetting::where('key', 'mautic')->first();
        if (! $row) {
            return [];
        }

        $value = $row->value ?? [];

        if ($value instanceof \ArrayObject) {
            return $value->getArrayCopy();
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function saveSettings(array $settings): void
    {
        $current = $this->getSettings();
        $merged  = array_merge($current, $settings);

        IntegrationSetting::updateOrCreate(
            ['key' => 'mautic'],
            ['value' => $merged]
        );
    }

    /**
     * La base debe respetar si la instancia usa /index.php en la URL.
     * Si la instancia no lo necesita, no lo añadimos automáticamente.
     */
    private function baseForRoutes(array $settings): string
    {
        $base = rtrim((string) ($settings['base_url'] ?? ''), '/');

        if ($base === '') {
            return '';
        }

        if (str_contains($base, 'index.php')) {
            return $base;
        }

        return $base;
    }

    private function buildApiUrl(array $settings, string $path): string
    {
        $base = $this->baseForRoutes($settings);

        if ($base === '') {
            return '';
        }

        $normalizedPath = '/' . ltrim($path, '/');

        return $base . '/api' . $normalizedPath;
    }

    private function apiRequest(string $method, string $path, array $query = []): array
    {
        $settings = $this->getSettings();

        if (empty($settings['base_url'])) {
            return ['ok' => false, 'message' => 'Completa la URL base de Mautic.'];
        }

        $token = $this->resolveAccessToken($settings);

        if (empty($token)) {
            return ['ok' => false, 'message' => 'Falta token de acceso de Mautic.'];
        }

        $url = $this->buildApiUrl($settings, $path);

        if ($url === '') {
            return ['ok' => false, 'message' => 'URL base inválida.'];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->$method($url, $query);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Mautic respondió con estado ' . $response->status() . '.',
                    'status' => $response->status(),
                    'payload' => $response->json(),
                ];
            }

            return ['ok' => true, 'payload' => $response->json()];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Error consultando Mautic: ' . $e->getMessage()];
        }
    }

    public function buildAuthorizeUrl(array $settings, string $redirectUri, string $state): string
    {
        $base = $this->baseForRoutes($settings);

        $authorizeEndpoint = $base . '/oauth/v2/authorize';

        // Nota: redirect_uri debe ir URL encoded (la doc lo recalca) :contentReference[oaicite:3]{index=3}
        $query = http_build_query([
            'client_id'     => $settings['public_key'],
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'state'         => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return $authorizeEndpoint . '?' . $query;
    }

    public function exchangeAuthorizationCodeForToken(string $code, string $redirectUri): array
    {
        $settings = $this->getSettings();

        if (empty($settings['base_url']) || empty($settings['public_key']) || empty($settings['secret_key'])) {
            return ['ok' => false, 'message' => 'Faltan base_url / public_key / secret_key.'];
        }

        $base = $this->baseForRoutes($settings);
        $tokenEndpoint = $base . '/oauth/v2/token';

        try {
            $response = Http::asForm()->post($tokenEndpoint, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $settings['public_key'],
                'client_secret' => $settings['secret_key'],
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ]);

            if (! $response->successful()) {
                $err = $response->json('error_description')
                    ?? $response->json('error')
                    ?? $response->body();

                return [
                    'ok' => false,
                    'message' => 'Mautic no devolvió token. HTTP ' . $response->status() . '. ' . mb_strimwidth((string) $err, 0, 220, '…'),
                ];
            }

            $accessToken  = $response->json('access_token');
            $refreshToken = $response->json('refresh_token');
            $expiresIn    = (int) ($response->json('expires_in') ?? 0);

            if (empty($accessToken) || empty($refreshToken)) {
                return ['ok' => false, 'message' => 'Respuesta OAuth incompleta (sin access_token/refresh_token).'];
            }

            $this->saveSettings([
                'oauth_access_token'  => $accessToken,
                'oauth_refresh_token' => $refreshToken,
                'oauth_expires_at'    => $expiresIn > 0
                    ? Carbon::now()->addSeconds(max(0, $expiresIn - 60))->toIso8601String()
                    : null,
            ]);

            return ['ok' => true, 'message' => 'Mautic autorizado correctamente. Ya puedes “Probar conexión”.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Error obteniendo token: ' . $e->getMessage()];
        }
    }

    private function tokenExpired(array $settings): bool
    {
        if (empty($settings['oauth_expires_at'])) {
            return true;
        }

        try {
            return Carbon::parse($settings['oauth_expires_at'])->isPast();
        } catch (Throwable) {
            return true;
        }
    }

    private function refreshAccessToken(array $settings): ?array
    {
        if (empty($settings['oauth_refresh_token']) || empty($settings['public_key']) || empty($settings['secret_key'])) {
            return null;
        }

        $base = $this->baseForRoutes($settings);
        $tokenEndpoint = $base . '/oauth/v2/token';

        try {
            $response = Http::asForm()->post($tokenEndpoint, [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $settings['oauth_refresh_token'],
                'client_id'     => $settings['public_key'],
                'client_secret' => $settings['secret_key'],
            ]);

            if (! $response->successful()) {
                return null;
            }

            $accessToken  = $response->json('access_token');
            $refreshToken = $response->json('refresh_token') ?: $settings['oauth_refresh_token'];
            $expiresIn    = (int) ($response->json('expires_in') ?? 0);

            if (empty($accessToken)) {
                return null;
            }

            $new = array_merge($settings, [
                'oauth_access_token'  => $accessToken,
                'oauth_refresh_token' => $refreshToken,
                'oauth_expires_at'    => $expiresIn > 0
                    ? Carbon::now()->addSeconds(max(0, $expiresIn - 60))->toIso8601String()
                    : null,
            ]);

            $this->saveSettings([
                'oauth_access_token'  => $new['oauth_access_token'],
                'oauth_refresh_token' => $new['oauth_refresh_token'],
                'oauth_expires_at'    => $new['oauth_expires_at'],
            ]);

            return $new;
        } catch (Throwable) {
            return null;
        }
    }

    public function resolveAccessToken(array $settings): ?string
    {
        // Token manual (si lo usáis)
        if (! empty($settings['api_token'])) {
            return $settings['api_token'];
        }

        // OAuth guardado
        if (! empty($settings['oauth_access_token']) && ! $this->tokenExpired($settings)) {
            return $settings['oauth_access_token'];
        }

        // Refresh
        $refreshed = $this->refreshAccessToken($settings);

        return $refreshed['oauth_access_token'] ?? null;
    }

    public function testConnection(array $settingsFromForm = []): array
    {
        $settings = array_merge($this->getSettings(), $settingsFromForm);

        if (empty($settings['base_url'])) {
            return ['ok' => false, 'message' => 'Completa la URL base de Mautic.'];
        }

        $token = $this->resolveAccessToken($settings);

        if (empty($token)) {
            return [
                'ok' => false,
                'message' => 'Falta autorizar la aplicación en Mautic. Pulsa “Conectar con Mautic” para generar el token.',
            ];
        }

        $base = $this->baseForRoutes($settings);
        $endpoint = $base . '/api/users/self';

        try {
            $response = Http::withToken($token)->get($endpoint);

            if ($response->successful()) {
                return ['ok' => true, 'message' => 'Conexión con Mautic verificada correctamente.'];
            }

            return ['ok' => false, 'message' => 'La conexión falló. Código: ' . $response->status()];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'La conexión falló. ' . $e->getMessage()];
        }
    }

    public function availableCampaigns(): array
    {
        $response = $this->apiRequest('get', '/campaigns', [
            'limit' => 200,
            'orderBy' => 'name',
            'orderByDir' => 'ASC',
        ]);

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        $payload = $response['payload'] ?? [];
        $campaigns = $payload['campaigns'] ?? [];

        if (! is_array($campaigns)) {
            return [];
        }

        return collect($campaigns)
            ->values()
            ->map(fn (array $campaign) => [
                'id' => $campaign['id'] ?? null,
                'name' => $campaign['name'] ?? $campaign['title'] ?? 'Campaña',
                'is_published' => $campaign['isPublished'] ?? $campaign['is_published'] ?? null,
                'description' => $campaign['description'] ?? null,
            ])
            ->filter(fn (array $campaign) => ! empty($campaign['id']))
            ->all();
    }

    public function campaignPreview(int $campaignId): array
    {
        if ($campaignId <= 0) {
            return [];
        }

        $response = $this->apiRequest('get', '/campaigns/' . $campaignId);

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        $campaign = $response['payload']['campaign'] ?? [];

        if (! is_array($campaign)) {
            return [];
        }

        return [
            'id' => $campaign['id'] ?? $campaignId,
            'name' => $campaign['name'] ?? $campaign['title'] ?? 'Campaña',
            'is_published' => $campaign['isPublished'] ?? $campaign['is_published'] ?? null,
            'description' => $campaign['description'] ?? null,
        ];
    }

    public function campaignMetrics(\App\Models\Campaign $campaign): array
    {
        $settings = $this->getSettings();
        $segmentId = $campaign->mautic_segment_id ?: ($settings['default_segment'] ?? null);

        if (empty($segmentId)) {
            return ['status' => 'Sin segmento configurado en Mautic.'];
        }

        $segmentResponse = $this->apiRequest('get', '/segments/' . $segmentId . '/contacts', [
            'limit' => 1,
        ]);

        if (! ($segmentResponse['ok'] ?? false)) {
            return ['status' => 'No fue posible obtener métricas desde Mautic.'];
        }

        $payload = $segmentResponse['payload'] ?? [];
        $total = $payload['total'] ?? null;

        if ($total === null && isset($payload['contacts']) && is_array($payload['contacts'])) {
            $total = count($payload['contacts']);
        }

        return [
            'status' => 'Actualizado desde Mautic',
            'segment_total' => $total,
            'emails_sent' => null,
            'emails_opened' => null,
            'emails_clicked' => null,
            'emails_bounced' => null,
            'unsubscribed' => null,
        ];
    }
}
