<?php

namespace App\Http\Controllers;

use App\Services\MauticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(MauticService $mauticService): View
    {
        return view('configuracion.index', [
            'mautic' => $mauticService->getSettings(),
        ]);
    }

    public function updateMautic(Request $request, MauticService $mauticService): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'base_url'        => ['required', 'url'],
            'api_token'       => ['nullable', 'string'],
            'public_key'      => ['nullable', 'string'],
            'secret_key'      => ['nullable', 'string'],
            'default_segment' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('configuracion.index')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Permitimos guardar solo base_url + claves OAuth (sin api_token),
        // porque el token se generará al autorizar con el botón.
        if (empty($data['api_token']) && (empty($data['public_key']) || empty($data['secret_key']))) {
            return redirect()
                ->route('configuracion.index')
                ->with('error', 'Introduce un token de API o las claves pública y secreta de Mautic.');
        }

        // Guardamos sin borrar tokens oauth ya existentes
        $mauticService->saveSettings($data);

        return redirect()
            ->route('configuracion.index')
            ->with('status', 'Integración con Mautic guardada correctamente.');
    }

    public function testMautic(Request $request, MauticService $mauticService): RedirectResponse
    {
        // IMPORTANTE: aquí NO obligamos api_token (antes lo estabas obligando dos veces)
        $validator = Validator::make($request->all(), [
            'base_url'        => ['required', 'url'],
            'api_token'       => ['nullable', 'string'],
            'public_key'      => ['nullable', 'string'],
            'secret_key'      => ['nullable', 'string'],
            'default_segment' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('configuracion.index')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $result = $mauticService->testConnection($data);

        return redirect()
            ->route('configuracion.index')
            ->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function connectMautic(Request $request, MauticService $mauticService): RedirectResponse
    {
        $settings = $mauticService->getSettings();

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'base_url'        => ['required', 'url'],
                'api_token'       => ['nullable', 'string'],
                'public_key'      => ['required', 'string'],
                'secret_key'      => ['required', 'string'],
                'default_segment' => ['nullable', 'integer'],
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('configuracion.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();
            $mauticService->saveSettings($data);
            $settings = array_merge($settings, $data);
        }

        if (empty($settings['base_url']) || empty($settings['public_key']) || empty($settings['secret_key'])) {
            return redirect()
                ->route('configuracion.index')
                ->with('error', 'Configura base_url + clave pública + clave secreta antes de conectar.');
        }

        $state = Str::random(40);
        $request->session()->put('mautic_oauth_state', $state);

        $redirectUri = route('configuracion.mautic.callback');

        $authorizeUrl = $mauticService->buildAuthorizeUrl($settings, $redirectUri, $state);

        return redirect()->away($authorizeUrl);
    }

    public function callbackMautic(Request $request, MauticService $mauticService): RedirectResponse
    {
        $expectedState = $request->session()->pull('mautic_oauth_state');

        $state = (string) $request->query('state', '');
        $code  = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDescription = (string) $request->query('error_description', '');

        if (empty($expectedState) || ! hash_equals($expectedState, $state)) {
            return redirect()
                ->route('configuracion.index')
                ->with('error', 'State inválido. Repite la conexión con Mautic.');
        }

        if (! empty($error)) {
            $message = 'Mautic devolvió un error al autorizar.';
            if (! empty($errorDescription)) {
                $message .= ' ' . $errorDescription;
            }

            return redirect()
                ->route('configuracion.index')
                ->with('error', $message);
        }

        if (empty($code)) {
            return redirect()
                ->route('configuracion.index')
                ->with('error', 'Mautic no devolvió el parámetro "code". Revisa credenciales y Redirect URI.');
        }

        $redirectUri = route('configuracion.mautic.callback');

        $result = $mauticService->exchangeAuthorizationCodeForToken($code, $redirectUri);

        return redirect()
            ->route('configuracion.index')
            ->with($result['ok'] ? 'status' : 'error', $result['message']);
    }
}
