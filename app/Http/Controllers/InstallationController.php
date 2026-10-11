<?php

namespace App\Http\Controllers;

use App\Models\ParametreSysteme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Page d'amorçage, volontairement discrète : elle permet de créer le PREMIER
 * compte administrateur pour activer le système.
 *
 * Elle n'existe que tant qu'aucun administrateur n'a été créé : dès qu'un compte
 * `admin` existe, la page se ferme définitivement (404). Si un jeton secret est
 * configuré (`SYSTEME_INSTALL_TOKEN`), il est exigé dans l'URL (`?cle=…`).
 */
class InstallationController extends Controller
{
    public function show(Request $request): View
    {
        $this->autoriser($request);

        return view('installation.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autoriser($request);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'Indiquez une adresse e-mail.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'username.required' => 'Choisissez un identifiant de connexion.',
            'username.unique' => 'Cet identifiant est déjà pris.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        User::create($data + ['role' => 'admin', 'statut' => 'actif']);

        // Active le système : crée la ligne de réglages si elle n'existe pas encore.
        try {
            if (ParametreSysteme::query()->doesntExist()) {
                ParametreSysteme::create(ParametreSysteme::defaults()->getAttributes());
            }
        } catch (\Throwable) {
            // Réglages facultatifs : leur absence ne doit pas empêcher l'amorçage.
        }

        // Un compte vient d'être créé : on referme la réouverture temporaire.
        Cache::forget('installation.cle');

        return redirect()
            ->route('login')
            ->with('success', 'Compte administrateur créé : le système est activé. Connectez-vous.');
    }

    /**
     * Autorise l'accès : jeton configuré, amorçage normal (aucun admin), ou
     * réouverture contrôlée par le jeton temporaire de la commande `systeme:amorcage`.
     */
    private function autoriser(Request $request): void
    {
        $jeton = (string) config('install.token');
        $fourni = (string) $request->query('cle', (string) $request->input('cle', ''));

        if ($jeton !== '') {
            abort_unless($fourni !== '' && hash_equals($jeton, $fourni), 404);

            return;
        }

        // Amorçage normal : la page n'existe que tant qu'aucun administrateur n'est créé.
        if (User::query()->where('role', 'admin')->doesntExist()) {
            return;
        }

        // Réouverture temporaire : jeton délivré par `php artisan systeme:amorcage`.
        $invitation = (string) Cache::get('installation.cle', '');
        abort_unless($invitation !== '' && $fourni !== '' && hash_equals($invitation, $fourni), 404);
    }
}
