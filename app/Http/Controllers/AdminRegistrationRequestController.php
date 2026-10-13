<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\DemandeCompte;
use App\Models\DemandeInscription;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\ParametreSysteme;
use App\Models\Professeurs;
use App\Models\Tuteur;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRegistrationRequestController extends Controller
{
    public function studentRequests(Request $request): View
    {
        $this->autoriserValidationInscriptions($request);

        return view('admin.registration.student-requests', [
            'requests' => DemandeInscription::with(['classe', 'anneeScolaire'])
                ->where('statut', 'en_attente')
                ->oldest()
                ->get(),
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'schoolYears' => AnneeScolaire::where('statut', 'actif')->orderByDesc('is_encours')->orderByDesc('date_debut')->get(),
        ]);
    }

    public function studentHistory(Request $request): View
    {
        $this->autoriserValidationInscriptions($request);

        $statut = (string) $request->query('statut', '');

        return view('admin.registration.student-history', [
            'requests' => DemandeInscription::with(['classe', 'anneeScolaire', 'traitePar'])
                ->whereIn('statut', ['acceptee', 'refusee'])
                ->when(in_array($statut, ['acceptee', 'refusee'], true), fn ($query) => $query->where('statut', $statut))
                ->latest('traite_le')
                ->paginate(25)
                ->withQueryString(),
            'statut' => $statut,
            'counts' => [
                'acceptee' => DemandeInscription::where('statut', 'acceptee')->count(),
                'refusee' => DemandeInscription::where('statut', 'refusee')->count(),
            ],
        ]);
    }

    public function approveStudent(Request $request, DemandeInscription $demandeInscription): RedirectResponse
    {
        $this->autoriserValidationInscriptions($request);

        $data = $request->validate([
            'classe_id' => ['required', Rule::exists('classes', 'id')->where('statut', 'actif')],
            'annee_scolaire_id' => ['required', Rule::exists('annees_scolaires', 'id')->where('statut', 'actif')],
        ], [
            'classe_id.required' => 'Choisissez une classe pour valider l’inscription.',
            'annee_scolaire_id.required' => 'Choisissez une année scolaire pour valider l’inscription.',
        ]);

        $result = DB::transaction(function () use ($request, $demandeInscription, $data) {
            $application = DemandeInscription::query()->lockForUpdate()->findOrFail($demandeInscription->id);
            if ($application->statut !== 'en_attente') {
                return false;
            }

            $guardian = Tuteur::create([
                'nom_mere' => $application->nom_mere,
                'telephone_mere' => $application->telephone_mere,
                'email_mere' => $application->email_mere,
                'adresse_mere' => $application->adresse_mere,
                'nom_pere' => $application->nom_pere,
                'telephone_pere' => $application->telephone_pere,
                'email_pere' => $application->email_pere,
                'statut' => 'actif',
            ]);

            $schoolYear = AnneeScolaire::findOrFail($data['annee_scolaire_id']);
            $matriculePrefix = 'ELV-'.Str::before($schoolYear->libelle, '-').'-';
            $sequence = Eleve::query()->where('matricule', 'like', $matriculePrefix.'%')->count() + 1;
            do {
                $matricule = $matriculePrefix.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
            } while (Eleve::where('matricule', $matricule)->exists());

            $student = Eleve::create([
                'nom' => $application->nom,
                'postnom' => $application->postnom,
                'prenom' => $application->prenom,
                'sexe' => $application->sexe,
                'date_naissance' => $application->date_naissance,
                'lieu_naissance' => $application->lieu_naissance,
                'tuteur_id' => $guardian->id,
                'matricule' => $matricule,
                'statut' => 'actif',
            ]);

            DB::table('inscriptions')->insert([
                'eleve_id' => $student->id,
                'classe_id' => $data['classe_id'],
                'annee_scolaire_id' => $data['annee_scolaire_id'],
                'date_inscription' => now()->toDateString(),
                'statut' => Inscription::STATUT_EN_ATTENTE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $application->update([
                'classe_id' => $data['classe_id'],
                'annee_scolaire_id' => $data['annee_scolaire_id'],
                'statut' => 'acceptee',
                'traite_par' => $request->user()->id,
                'traite_le' => now(),
            ]);

            return true;
        });

        return redirect()->route('admin.registration.students')
            ->with($result ? 'success' : 'error', $result ? 'La demande a été acceptée : l’inscription est en attente de validation, elle sera activée automatiquement à l’encaissement du frais d’inscription.' : 'Cette demande a déjà été traitée.');
    }

    public function rejectStudent(Request $request, DemandeInscription $demandeInscription): RedirectResponse
    {
        $this->autoriserValidationInscriptions($request);

        $data = $request->validate(['motif_refus' => ['nullable', 'string', 'max:1000']]);
        $updated = DemandeInscription::query()
            ->whereKey($demandeInscription->id)
            ->where('statut', 'en_attente')
            ->update([
                'statut' => 'refusee',
                'traite_par' => $request->user()->id,
                'traite_le' => now(),
                'motif_refus' => $data['motif_refus'] ?? null,
                'updated_at' => now(),
            ]);

        return back()->with($updated ? 'success' : 'error', $updated ? 'La demande d’admission a été refusée.' : 'Cette demande a déjà été traitée.');
    }

    public function accountRequests(): View
    {
        return view('admin.registration.account-requests', [
            'requests' => DemandeCompte::where('statut', 'en_attente')->oldest()->get(),
            'professors' => Professeurs::query()
                ->where('statut', 'actif')
                ->whereNotIn('id', User::query()->whereNotNull('professeur_id')->select('professeur_id'))
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get(),
        ]);
    }

    public function approveAccount(Request $request, DemandeCompte $demandeCompte): RedirectResponse
    {
        if ($demandeCompte->refresh()->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande de compte a déjà été traitée.');
        }

        $data = $request->validate([
            'role' => ['required', 'in:prefetture,titulaire,professeur,comptable,parent'],
            'professeur_id' => [
                'nullable',
                Rule::exists('professeurs', 'id')->where('statut', 'actif'),
                Rule::unique('users', 'professeur_id'),
                Rule::requiredIf(fn () => $request->input('role') === 'professeur'),
            ],
        ], [
            'professeur_id.required' => 'Sélectionnez une fiche professeur à associer au compte.',
            'professeur_id.unique' => 'Cette fiche professeur est déjà associée à un autre compte.',
        ]);

        $result = DB::transaction(function () use ($request, $demandeCompte, $data) {
            $application = DemandeCompte::query()->lockForUpdate()->findOrFail($demandeCompte->id);
            if ($application->statut !== 'en_attente') {
                return false;
            }

            User::create([
                'nom' => $application->nom,
                'postnom' => $application->postnom,
                'prenom' => $application->prenom,
                'username' => $application->username,
                'email' => $application->email,
                'telephone' => $application->telephone,
                'professeur_id' => $data['role'] === 'professeur' ? $data['professeur_id'] : null,
                'password' => $application->mot_de_passe,
                'role' => $data['role'],
                'statut' => 'actif',
            ]);

            $application->update([
                'statut' => 'acceptee',
                'traite_par' => $request->user()->id,
                'traite_le' => now(),
                'mot_de_passe' => null,
            ]);

            return true;
        });

        return redirect()->route('admin.registration.accounts')
            ->with($result ? 'success' : 'error', $result ? 'Le compte a été créé et activé.' : 'Cette demande a déjà été traitée.');
    }

    public function rejectAccount(Request $request, DemandeCompte $demandeCompte): RedirectResponse
    {
        $data = $request->validate(['motif_refus' => ['nullable', 'string', 'max:1000']]);
        $updated = DemandeCompte::query()
            ->whereKey($demandeCompte->id)
            ->where('statut', 'en_attente')
            ->update([
                'statut' => 'refusee',
                'traite_par' => $request->user()->id,
                'traite_le' => now(),
                'motif_refus' => $data['motif_refus'] ?? null,
                'mot_de_passe' => null,
                'updated_at' => now(),
            ]);

        return back()->with($updated ? 'success' : 'error', $updated ? 'La demande de compte a été refusée.' : 'Cette demande a déjà été traitée.');
    }

    /**
     * Autorise la validation des demandes d'inscription (élèves) :
     *  - l'administrateur, toujours ;
     *  - le Préfet des Études, UNIQUEMENT si l'administration l'a autorisé
     *    (réglage « prefet_valide_inscriptions ») ;
     *  - tout autre poste disposant de la fonctionnalité « inscriptions ».
     */
    private function autoriserValidationInscriptions(Request $request): void
    {
        $role = $request->user()?->role;

        if ($role === 'admin') {
            return;
        }

        if ($role === 'prefetture') {
            abort_unless(
                ParametreSysteme::prefetValideInscriptions(),
                403,
                'L’administration n’a pas autorisé le préfet à valider les demandes d’inscription.'
            );

            return;
        }

        abort_unless(Permissions::autorise($role, 'inscriptions'), 403, 'Accès non autorisé.');
    }
}
