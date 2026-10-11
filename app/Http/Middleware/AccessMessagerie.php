<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit l'accès à la messagerie sur TOUTES les pages HTML, pour tous les
 * rôles : un bouton flottant est injecté si la page n'offre pas déjà de lien
 * vers la messagerie (aucun doublon sur les pages qui l'intègrent déjà).
 */
class AccessMessagerie
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contenu = $response->getContent();

        if (! is_string($contenu) || $contenu === '') {
            return $response;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        if (! str_contains($contenu, '</body>') || str_contains($contenu, 'messagerie-flottante')) {
            return $response;
        }

        if (! $request->hasSession() || ! $request->user()) {
            return $response;
        }

        // Pages publiques (vitrine, démarches, connexion) : la messagerie fait
        // partie de l'espace connecté, aucun bouton flottant n'y est injecté —
        // même pour un utilisateur qui navigue sur le site en étant connecté.
        if ($request->routeIs(
            'site.accueil', 'site.inscriptions', 'site.apropos', 'site.contact', 'site.contact.store',
            'registration.student.create', 'registration.student.store',
            'registration.account.create', 'registration.account.store',
            'login', 'login.store', 'login.pin', 'login.pin.store'
        )) {
            return $response;
        }

        // La page propose déjà un accès (menu, raccourci ou bouton) : rien à faire.
        if (str_contains($contenu, route('messagerie.index'))) {
            return $response;
        }

        $bouton = view('partials.messagerie-flottante')->render();

        if (trim($bouton) === '') {
            return $response;
        }

        $response->setContent(str_replace('</body>', $bouton.'</body>', $contenu));

        return $response;
    }
}
