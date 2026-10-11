<?php

namespace App\Http\Middleware;

use App\Support\Permissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à une fonctionnalité de la matrice des permissions
 * (config/permissions.php). Le rôle `admin` passe toujours.
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! Permissions::autorise($request->user()?->role, $feature)) {
            abort(403, 'Accès non autorisé à cette fonctionnalité.');
        }

        return $next($request);
    }
}
