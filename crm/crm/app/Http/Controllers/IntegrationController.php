<?php

namespace App\Http\Controllers;

use App\Models\IntegrationToken;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = IntegrationToken::with('user')
            ->orderByDesc('created_at')
            ->get();

        $baseUrl = rtrim(config('app.url'), '/') . '/api';

        $sampleEndpoints = [
            [
                'method'      => 'GET',
                'uri'         => '/api/accounts',
                'description' => 'Listar cuentas del CRM',
            ],
            [
                'method'      => 'GET',
                'uri'         => '/api/solicitudes',
                'description' => 'Listar solicitudes (leads) registradas',
            ],
            [
                'method'      => 'POST',
                'uri'         => '/api/solicitudes',
                'description' => 'Crear una nueva solicitud desde otra plataforma',
            ],
            [
                'method'      => 'GET',
                'uri'         => '/api/pedidos',
                'description' => 'Listar pedidos comerciales',
            ],
        ];

        return view('integraciones.index', [
            'tokens'          => $tokens,
            'baseUrl'         => $baseUrl,
            'sampleEndpoints' => $sampleEndpoints,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'scopes'     => ['nullable', 'array'],
            'scopes.*'   => ['string'],
        ]);

        $plainToken = Str::random(40);

        IntegrationToken::create([
            'name'   => $data['name'],
            'token'  => $plainToken,
            'scopes' => $data['scopes'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('integraciones.index')
            ->with('status', 'Token creado correctamente: ' . $plainToken);
    }

    public function destroy(IntegrationToken $token): RedirectResponse
    {
        $token->delete();

        return redirect()
            ->route('integraciones.index')
            ->with('status', 'Token de API revocado correctamente.');
    }
}
