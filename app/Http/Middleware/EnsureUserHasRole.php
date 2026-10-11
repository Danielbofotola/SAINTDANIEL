<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // En consultation (impersonation), seules les routes strictement réservées
        // à l'admin sont bloquées. Une route ouverte à d'autres rôles — par ex.
        // « role:parent,admin » — doit rester accessible au compte consulté dès
        // lors qu'il possède l'un des rôles autorisés.
        if ($request->session()->has('impersonating_admin_id') && $roles === ['admin']) {
            abort(403, 'Les accès administrateur sont désactivés pendant une session de consultation.');
        }

        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Vous n’avez pas accès à cette page.');
        }

        return $next($request);
    }
}
