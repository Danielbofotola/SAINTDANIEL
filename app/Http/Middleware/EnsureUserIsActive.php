<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un compte non « actif » ne peut jamais naviguer connecté : le statut est
 * vérifié à chaque requête (et pas seulement au moment du login). Si un
 * administrateur désactive un compte pendant que l'utilisateur est en ligne,
 * sa session est immédiatement invalidée.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->statut !== 'actif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Votre compte a été désactivé. Contactez un administrateur pour le réactiver.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('login')->withErrors(['login' => $message]);
        }

        return $next($request);
    }
}
