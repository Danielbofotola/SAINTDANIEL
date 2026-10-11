<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Paiement;
use App\Support\Encaisse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Profil propre au caissier (comptable) : son compte, ses indicateurs de caisse
 * de l'année en cours, et la modification de ses informations et mot de passe.
 */
class ComptableProfilController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $annee = AnneeScolaire::enCours();

        $base = fn () => Paiement::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->whereHas(
                'inscription',
                fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id)
            ));

        return view('comptable.profil', [
            'user' => $user,
            'annee' => $annee,
            'nbPaiements' => $base()->count(),
            'encaissementsJour' => $base()->whereDate('date_paiement', today())->count(),
            'encaisseParDevise' => Encaisse::parDevise($annee),
            'derniersPaiements' => $base()
                ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire'])
                ->latest('date_paiement')
                ->limit(8)
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nom' => ['nullable', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'email.required' => "L'adresse e-mail est obligatoire.",
            'email.unique' => "Cette adresse e-mail est déjà utilisée.",
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('users/photos', 'public');
        }

        $user->update($data);

        return redirect()->route('comptable.profil')->with('success', 'Votre profil a été mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }
}
