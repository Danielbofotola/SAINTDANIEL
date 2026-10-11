<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Affiche les messages flash (succès / erreur / erreurs de validation) sur
 * TOUTE page HTML du système, sous forme de bandeau flottant.
 *
 * Un message déjà rendu en ligne par la page (bloc « alert » classique) n'est
 * pas répété : on ne l'injecte pas une seconde fois.
 */
class FlashGlobal
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

        if (! str_contains($contenu, '</body>') || str_contains($contenu, 'id="flash-global"')) {
            return $response;
        }

        $session = $request->hasSession() ? $request->session() : null;

        if (! $session) {
            return $response;
        }

        $messages = [];

        foreach (['success' => 'success', 'error' => 'error'] as $cle => $type) {
            $texte = $session->get($cle);

            if (is_string($texte) && $texte !== '' && ! str_contains($contenu, e($texte))) {
                $messages[] = ['type' => $type, 'texte' => $texte];
            }
        }

        $erreurs = $session->get('errors');

        if ($erreurs instanceof \Illuminate\Support\ViewErrorBag && $erreurs->any()) {
            $texte = implode(' ', $erreurs->all());

            if ($texte !== '' && ! str_contains($contenu, e($texte))) {
                $messages[] = ['type' => 'error', 'texte' => $texte];
            }
        }

        if ($messages === []) {
            return $response;
        }

        $bandeau = view('partials.flash', ['messages' => $messages])->render();

        $response->setContent(str_replace('</body>', $bandeau.'</body>', $contenu));

        return $response;
    }
}
