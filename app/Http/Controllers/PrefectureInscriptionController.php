<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\DemandeInscription;
use App\Models\ParametreSysteme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace Préfet — validation des demandes d'inscription (élèves).
 *
 * Accessible au Préfet des Études UNIQUEMENT si l'administration l'a autorisé
 * (réglage « prefet_valide_inscriptions »). Le traitement reprend exactement la
 * logique de l'administration (AdminRegistrationRequestController) : il n'y a
 * pas de seconde implémentation à maintenir.
 */
class PrefectureInscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $this->autoriser();

        return view('prefecture.demandes-inscription', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'requests' => DemandeInscription::with(['classe', 'anneeScolaire'])
                ->where('statut', 'en_attente')
                ->oldest()
                ->get(),
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'schoolYears' => AnneeScolaire::where('statut', 'actif')
                ->orderByDesc('is_encours')
                ->orderByDesc('date_debut')
                ->get(),
        ]);
    }

    /** Accepter une demande : crée l'élève, le tuteur et l'inscription en attente. */
    public function approve(Request $request, DemandeInscription $demandeInscription): RedirectResponse
    {
        $this->autoriser();

        // Même logique que l'administration (validation + transaction comprises).
        app(AdminRegistrationRequestController::class)->approveStudent($request, $demandeInscription);

        $acceptee = $demandeInscription->fresh()?->statut === 'acceptee';

        return redirect()->route('prefecture.demandes.inscriptions')->with(
            $acceptee ? 'success' : 'error',
            $acceptee
                ? 'La demande a été acceptée : l’inscription est en attente de validation, elle sera activée à l’encaissement.'
                : 'Cette demande a déjà été traitée.'
        );
    }

    /** Refuser une demande d'inscription. */
    public function reject(Request $request, DemandeInscription $demandeInscription): RedirectResponse
    {
        $this->autoriser();

        app(AdminRegistrationRequestController::class)->rejectStudent($request, $demandeInscription);

        $refusee = $demandeInscription->fresh()?->statut === 'refusee';

        return redirect()->route('prefecture.demandes.inscriptions')->with(
            $refusee ? 'success' : 'error',
            $refusee ? 'La demande d’admission a été refusée.' : 'Cette demande a déjà été traitée.'
        );
    }

    /** Le Préfet n'y accède que si l'administration l'a autorisé. */
    private function autoriser(): void
    {
        abort_unless(
            ParametreSysteme::prefetValideInscriptions(),
            403,
            'L’administration n’a pas autorisé le préfet à valider les demandes d’inscription.'
        );
    }
}
