<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('projet.page-connexion.page-login-password');
    }

    public function showPinForm(): View
    {
        return view('projet.page-connexion.connexion-pin');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Veuillez saisir votre identifiant ou votre adresse e-mail.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $field = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([
            $field => $validated['login'],
            'password' => $validated['password'],
            'statut' => 'actif',
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'Les identifiants sont incorrects ou votre compte est inactif.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->update(['last_login' => now()]);

        return $this->redirectBasedOnRole($user->role);
    }

    public function loginWithPin(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'pin_code' => ['required', 'string', 'digits:4'],
        ], [
            'pin_code.required' => 'Le code PIN est obligatoire.',
            'pin_code.digits' => 'Le code PIN doit contenir exactement 4 chiffres.',
        ]);

        $matchingUsers = User::where('statut', 'actif')
            ->whereNotNull('pin_code')
            ->get()
            ->filter(fn (User $user) => Hash::check($validated['pin_code'], $user->pin_code));

        if ($matchingUsers->count() !== 1) {
            $message = $matchingUsers->isEmpty()
                ? 'Code PIN invalide.'
                : 'Ce code PIN est associé à plusieurs comptes. Contactez un administrateur.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['pin_code' => $message]);
        }

        /** @var User $user */
        $user = $matchingUsers->first();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route($this->dashboardRouteForRole($user->role))]);
        }

        return $this->redirectBasedOnRole($user->role);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous êtes déconnecté.');
    }

    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        return redirect()->intended(route($this->dashboardRouteForRole($role)));
    }

    protected function dashboardRouteForRole(string $role): string
    {
        return User::dashboardRouteNameFor($role);
    }
}
