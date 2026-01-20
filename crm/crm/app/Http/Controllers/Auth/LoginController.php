<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactorService)
    {
    }

    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::validate($credentials)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas.',
            ])->onlyInput('email');
        }

        /** @var User $user */
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No se encontró el usuario solicitado.',
            ])->onlyInput('email');
        }

        $code = $this->twoFactorService->send($user);

        $request->session()->put('two_factor:user:id', $user->id);
        $request->session()->put('two_factor:remember', $remember);
        $request->session()->put('two_factor:auth:ip', $request->ip());

        if (app()->environment('local') || in_array(config('mail.default'), ['log', 'array'], true)) {
            $request->session()->flash('two_factor:preview_code', $code);
        }


        return redirect()
            ->route('two-factor.show')
            ->with('status', 'Te enviamos un código de verificación.');
    }

    public function destroy(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
