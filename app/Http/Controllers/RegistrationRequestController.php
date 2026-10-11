<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\DemandeCompte;
use App\Models\DemandeInscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function createStudentRequest(): View
    {
        return view('registration.student-request', [
            'classes' => Classe::query()
                ->where('statut', 'actif')
                ->whereHas('cycle', fn ($query) => $query->where('statut', 'actif'))
                ->orderBy('nom')
                ->get(),
            'schoolYears' => AnneeScolaire::query()
                ->where('statut', 'actif')
                ->orderByDesc('is_encours')
                ->orderByDesc('date_debut')
                ->get(),
        ]);
    }

    public function storeStudentRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'sexe' => ['required', 'in:M,F'],
            'date_naissance' => ['required', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'classe_id' => ['required', Rule::exists('classes', 'id')->where('statut', 'actif')],
            'nom_mere' => ['required', 'string', 'max:100'],
            'telephone_mere' => ['required', 'string', 'max:20'],
            'email_mere' => ['nullable', 'email', 'max:100'],
            'adresse_mere' => ['nullable', 'string', 'max:1000'],
            'nom_pere' => ['nullable', 'string', 'max:100'],
            'telephone_pere' => ['nullable', 'string', 'max:20'],
            'email_pere' => ['nullable', 'email', 'max:100'],
        ], [
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'classe_id.exists' => 'La classe sélectionnée n’est plus disponible.',
        ]);
        // L'année de la demande est imposée par le serveur : l'année en cours.
        $anneeEnCours = AnneeScolaire::enCours();

        if (! $anneeEnCours) {
            return back()->withInput()->withErrors([
                'annee_scolaire_id' => "Aucune année scolaire n'est en cours. L'administration doit d'abord ouvrir une année.",
            ]);
        }

        $data['annee_scolaire_id'] = $anneeEnCours->id;

        DemandeInscription::create($data);

        return redirect()->route('registration.student.create')
            ->with('success', 'Votre demande a été transmise. Elle sera examinée par l’administration avant toute inscription définitive.');
    }

    public function createAccountRequest(): View
    {
        return view('registration.account-request');
    }

    public function storeAccountRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['nullable', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'username' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'username'),
                Rule::unique('demandes_comptes', 'username')->where('statut', 'en_attente'),
            ],
            'email' => [
                'required', 'email', 'max:100',
                Rule::unique('users', 'email'),
                Rule::unique('demandes_comptes', 'email')->where('statut', 'en_attente'),
            ],
            'telephone' => ['nullable', 'string', 'max:20'],
            'role_souhaite' => ['required', 'in:prefetture,titulaire,professeur,comptable,parent'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.unique' => 'Ce nom d’utilisateur est déjà utilisé ou fait l’objet d’une demande en attente.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée ou fait l’objet d’une demande en attente.',
            'role_souhaite.in' => 'Le rôle demandé n’est pas disponible.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $data['mot_de_passe'] = Hash::make($data['mot_de_passe']);
        DemandeCompte::create($data);

        return redirect()->route('registration.account.create')
            ->with('success', 'Votre demande de compte a été transmise. Vous pourrez vous connecter après son approbation par l’administration.');
    }
}
