<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Professeurs;
use App\Models\Salle;
use App\Models\User;
use App\Support\PresenceUtilisateurs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Aperçu d'un utilisateur depuis la messagerie : fiche minimale
     * (nom complet, rôle, coordonnées) pour le profil du correspondant.
     */
    public function apercu(Request $request, User $user): JsonResponse
    {
        abort_unless($user->statut === 'actif', 404);

        return response()->json([
            'id' => $user->id,
            'nom' => trim(implode(' ', array_filter([$user->nom, $user->postnom, $user->prenom]))) ?: $user->username,
            'role' => User::libelleRole($user->role),
            'photo' => $user->photo ? asset('storage/'.$user->photo) : null,
            'initiales' => mb_strtoupper(mb_substr($user->prenom ?: $user->username, 0, 1)),
            'telephone' => $user->telephone,
            'email' => $user->email,
            'soi_meme' => $user->id === $request->user()->id,
        ]);
    }

    public function createAccount(): View
    {
        return view('admin.profile.account-create', [
            'professeurs' => Professeurs::where('statut', 'actif')->orderBy('nom')->orderBy('prenom')->get(),
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['nullable', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'professeur_id' => [
                'nullable',
                'exists:professeurs,id',
                Rule::unique('users', 'professeur_id'),
                Rule::requiredIf(fn () => $request->input('role') === 'professeur'),
            ],
            'role' => ['required', Rule::in(User::ROLES)],
            'statut' => ['required', 'in:actif,inactif'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin_code' => ['nullable', 'digits:4'],
        ], [
            'username.unique' => "Ce nom d'utilisateur est déjà utilisé.",
            'email.unique' => "Cette adresse e-mail est déjà utilisée.",
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'pin_code.digits' => 'Le code PIN doit contenir exactement 4 chiffres.',
            'professeur_id.required' => 'Un professeur doit être lié à un compte ayant le rôle professeur.',
            'professeur_id.unique' => 'Ce professeur est déjà lié à un autre compte utilisateur.',
        ]);

        $data['pin_code'] = filled($data['pin_code'] ?? null) ? Hash::make($data['pin_code']) : null;

        User::create($data);

        return redirect()->route('admin.profile.accounts')->with('success', 'Le compte utilisateur a été créé.');
    }

    public function accounts(): View
    {
        return view('admin.profile.accounts', [
            'users' => User::orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
                ->orderBy('username')
                ->get(),
        ]);
    }

    public function editAccount(User $user): View|RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('admin.profile.edit');
        }

        return view('admin.profile.account-edit', [
            'user' => $user,
            'professeurs' => Professeurs::query()
                ->where(function ($query) use ($user) {
                    $query->where('statut', 'actif')
                        ->when($user->professeur_id, fn ($query) => $query->orWhere('id', $user->professeur_id));
                })
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get(),
            // Rattachement « compte parent ↔ enfants » (uniquement pour un parent).
            'eleves' => $user->role === 'parent'
                ? Eleve::query()->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'postnom', 'prenom', 'matricule'])
                : collect(),
            'enfantsSelectionnes' => $user->role === 'parent' ? $user->eleves()->pluck('eleves.id')->all() : [],
        ]);
    }

    public function updateAccount(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.profile.edit');
        }

        $data = $request->validate([
            'nom' => ['nullable', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['nullable', 'string', 'max:50'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'role' => ['required', Rule::in(User::ROLES)],
            'statut' => ['required', 'in:actif,inactif'],
            'professeur_id' => [
                'nullable',
                'exists:professeurs,id',
                Rule::unique('users', 'professeur_id')->ignore($user->id),
                Rule::requiredIf(fn () => $request->input('role') === 'professeur'),
            ],
            'enfants' => ['nullable', 'array'],
            'enfants.*' => ['integer', 'exists:eleves,id'],
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $data['photo'] = $request->file('photo')->store('users/photos', 'public');
        }

        $etaitParent = $user->role === 'parent';

        $user->update($data);

        // Seul un compte parent porte des enfants ; un ancien compte parent en est
        // détaché lorsqu'il change de rôle.
        if ($data['role'] === 'parent') {
            $user->eleves()->sync($data['enfants'] ?? []);
        } elseif ($etaitParent) {
            $user->eleves()->sync([]);
        }

        return redirect()->route('admin.profile.accounts')->with('success', 'Le profil utilisateur a été mis à jour.');
    }

    /**
     * Page dédiée à la réinitialisation des identifiants d'un compte.
     */
    public function editAccountPassword(User $user): View|RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('admin.profile.edit')
                ->with('error', 'Utilisez votre profil pour modifier votre propre mot de passe.');
        }

        return view('admin.profile.account-password', ['user' => $user]);
    }

    /**
     * Réinitialise le mot de passe (et le code PIN) d'un autre compte.
     */
    public function updateAccountPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.profile.edit');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin_code' => ['nullable', 'digits:4'],
            'remove_pin' => ['nullable', 'boolean'],
        ], [
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'pin_code.digits' => 'Le code PIN doit contenir exactement 4 chiffres.',
        ]);

        unset($data['remove_pin']);

        // Le cast « hashed » du modèle hache le mot de passe.
        if (filled($data['pin_code'] ?? null)) {
            $data['pin_code'] = Hash::make($data['pin_code']);
        } elseif ($request->boolean('remove_pin')) {
            $data['pin_code'] = null;
        } else {
            unset($data['pin_code']);
        }

        $user->update($data);

        Log::notice('Admin reset an account password', [
            'admin_id' => $request->user()->id,
            'user_id' => $user->id,
            'pin_changed' => array_key_exists('pin_code', $data),
        ]);

        return redirect()
            ->route('admin.profile.accounts')
            ->with('success', "Les identifiants de {$user->username} ont été réinitialisés.");
    }

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        if ($request->session()->has('impersonating_admin_id')) {
            return back()->with('error', 'Une session de consultation est déjà en cours.');
        }

        if ($user->is($request->user()) || $user->role === 'admin' || $user->statut !== 'actif') {
            return back()->with('error', 'Seuls les autres comptes actifs non administrateurs peuvent être ouverts.');
        }

        $admin = $request->user();
        $request->session()->put('impersonating_admin_id', $admin->id);
        auth()->login($user);
        $request->session()->regenerate();

        Log::notice('Admin started account impersonation', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
        ]);

        return redirect()->route($user->dashboardRouteName());
    }

    public function stopImpersonation(Request $request): RedirectResponse
    {
        $admin = User::query()
            ->whereKey($request->session()->get('impersonating_admin_id'))
            ->where('role', 'admin')
            ->where('statut', 'actif')
            ->first();

        if (! $admin) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'La session administrateur d’origine n’est plus disponible.');
        }

        Log::notice('Admin stopped account impersonation', [
            'admin_id' => $admin->id,
            'user_id' => $request->user()->id,
        ]);

        auth()->login($admin);
        $request->session()->forget('impersonating_admin_id');
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Vous êtes de retour sur votre compte administrateur.');
    }

    public function show(Request $request): View
    {
        return view('admin.profile.show', [
            'user' => $request->user(),
            'users' => User::with('professeur')
                ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
                ->orderBy('username')
                ->limit(5)
                ->get(),
        ]);
    }

    public function statistics(Request $request): View
    {
        return view('admin.profile.statistics', [
            'user' => $request->user(),
            'administrationStats' => $this->administrationStats(),
        ]);
    }

    /**
     * Utilisateurs actuellement en ligne (sessions actives).
     */
    public function online(Request $request): View
    {
        $sessions = PresenceUtilisateurs::enLigne();
        $connectes = $sessions->where('est_connecte', true);

        return view('admin.profile.online', [
            'user' => $request->user(),
            'sessions' => $sessions,
            'connectes' => $connectes,
            'fenetre' => PresenceUtilisateurs::FENETRE_MINUTES,
        ]);
    }

    private function administrationStats(): array
    {
        $roleCounts = User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return [
            'labels' => ['Administrateurs', 'Préfecture', 'Titulaires', 'Professeurs', 'Comptables'],
            'roles' => [
                $roleCounts->get('admin', 0),
                $roleCounts->get('prefetture', 0),
                $roleCounts->get('titulaire', 0),
                $roleCounts->get('professeur', 0),
                $roleCounts->get('comptable', 0),
            ],
            'modules' => [
                Eleve::where('statut', 'actif')->count(),
                Classe::where('statut', 'actif')->count(),
                Professeurs::where('statut', 'actif')->count(),
                Salle::where('statut', 'actif')->count(),
            ],
        ];
    }

    public function showPersonalProfile(Request $request): View
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function editPersonalProfile(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nom' => ['nullable', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['nullable', 'string', 'max:50'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'username.required' => "Le nom d'utilisateur est obligatoire.",
            'username.unique' => "Ce nom d'utilisateur est déjà utilisé.",
            'email.required' => "L'adresse e-mail est obligatoire.",
            'email.email' => "L'adresse e-mail n'est pas valide.",
            'email.unique' => "Cette adresse e-mail est déjà utilisée.",
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $data['photo'] = $request->file('photo')->store('users/photos', 'public');
        }

        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Les propriétés du profil ont été modifiées.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Le mot de passe a été réinitialisé.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->user()->update(['statut' => 'inactif']);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Votre compte administrateur a été désactivé.');
    }

    public function toggleAccount(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte depuis cette page.');
        }

        $user->update(['statut' => $user->statut === 'actif' ? 'inactif' : 'actif']);

        if ($user->statut !== 'actif') {
            $this->purgerSessions($user);
        }

        return back()->with('success', 'Le statut du compte a été mis à jour.');
    }

    public function destroyAccount(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $this->purgerSessions($user);
        $user->delete();

        return back()->with('success', 'Le compte a été supprimé définitivement.');
    }

    /**
     * Met fin aux sessions actives d'un compte (driver « database ») : un
     * utilisateur désactivé ou supprimé ne reste pas connecté.
     */
    private function purgerSessions(User $user): void
    {
        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }
}