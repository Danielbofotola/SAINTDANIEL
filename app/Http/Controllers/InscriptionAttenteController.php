<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Inscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Nettoyage des inscriptions restées « en attente » : créées à l'inscription
 * mais jamais validées par la caisse (frais d'inscription non encaissé).
 *
 * La suppression n'enlève QUE l'inscription : la fiche élève et son tuteur
 * restent en base, prêts pour une réinscription ultérieure.
 */
class InscriptionAttenteController extends Controller
{
    public function index(): View
    {
        $annee = AnneeScolaire::enCours();

        $inscriptions = Inscription::query()
            ->with(['eleve', 'classe.cycle', 'classe.section', 'classe.option'])
            ->where('statut', Inscription::STATUT_EN_ATTENTE)
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->latest('date_inscription')
            ->get();

        return view('admin.inscriptions.attente', [
            'inscriptions' => $inscriptions,
            'annee' => $annee,
        ]);
    }

    /** Supprime une inscription en attente (l'élève et son tuteur sont conservés). */
    public function destroy(Inscription $inscription): RedirectResponse
    {
        if (! $inscription->estEnAttente()) {
            return back()->with('error', 'Seule une inscription « en attente » peut être supprimée depuis cet écran.');
        }

        $nom = trim(($inscription->eleve?->nom ?? '').' '.($inscription->eleve?->prenom ?? ''));
        $inscription->delete();

        return back()->with('success', 'Inscription en attente de '.$nom.' supprimée. L’élève reste enregistré, à réinscrire.');
    }

    /** Purge toutes les inscriptions en attente de l'année en cours. */
    public function purge(): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        $total = DB::transaction(fn () => Inscription::query()
            ->where('statut', Inscription::STATUT_EN_ATTENTE)
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->delete());

        return back()->with('success', $total.' inscription(s) en attente supprimée(s). Les élèves restent enregistrés.');
    }
}
