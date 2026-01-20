<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactorService)
    {
    }

    public function show(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (! $request->session()->has('two_factor:user:id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Inicia sesión para recibir un código de verificación.',
            ]);
        }

        return view('auth.two-factor');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('two_factor:user:id');
        $ipAddress = $request->session()->get('two_factor:auth:ip');

        if (! $userId) {
            return redirect()->route('login')->withErrors([
                'email' => 'La sesión de verificación expiró. Intenta de nuevo.',
            ]);
        }

        if ($ipAddress && $ipAddress !== $request->ip()) {
            $request->session()->forget([
                'two_factor:user:id',
                'two_factor:auth:ip',
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'El intento de acceso no coincide con el dispositivo que inició sesión.',
            ]);
        }

        $user = User::find($userId);

        if (! $user || ! $this->twoFactorService->validate($user, $data['code'])) {
            return back()->withErrors([
                'code' => 'Código inválido o caducado.',
            ]);
        }

        $remember = (bool) $request->session()->pull('two_factor:remember', false);

        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        $request->session()->forget([
            'two_factor:user:id',
            'two_factor:auth:ip',
        ]);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor:user:id');

        if (! $userId) {
            return redirect()->route('login')->withErrors([
                'email' => 'La sesión de verificación expiró. Intenta de nuevo.',
            ]);
        }

        $user = User::findOrFail($userId);

        $code = $this->twoFactorService->send($user);

        if (app()->environment('local') || in_array(config('mail.default'), ['log', 'array'], true)) {
            $request->session()->flash('two_factor:preview_code', $code);
        }

        return back()->with('status', 'Nuevo código enviado. Revisa tu email.');
    }
}