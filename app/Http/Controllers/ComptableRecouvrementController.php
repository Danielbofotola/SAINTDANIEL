<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\ParametreSysteme;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recouvrement : niveau d'encaissement global, évolution des paiements
 * (par période et par mois) et détail filtrable par classe et par période.
 */
class ComptableRecouvrementController extends Controller
{
    public function index(Request $request): View
    {
        // Module optionnel : la page « recouvrement » peut être désactivée.
        abort_unless(ParametreSysteme::recouvrementActif(), 404);

        $annee = AnneeScolaire::enCours();
        $classeId = $request->query('classe_id');
        $periodeId = $request->query('periode_id');

        $definis = 0.0;
        $encaisse = 0.0;
        $taux = 0.0;
        $parClasse = collect();
        $parPeriode = collect();
        $parMois = collect();
        $paiements = collect();

        if ($annee) {
            $definis = (float) FraisScolaire::query()
                ->anneeEnCours()
                ->when($classeId, fn ($query) => $query->where('classe_id', $classeId))
                ->sum('montant_exige');

            $requete = fn () => Paiement::query()
                ->where('statut', 'actif')
                ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                ->when($classeId, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('classe_id', $classeId)))
                ->when($periodeId, fn ($query) => $query->where('periode_id', $periodeId));

            $encaisse = (float) $requete()->sum('montant_paye');
            $taux = $definis > 0 ? round($encaisse / $definis * 100, 1) : 0.0;

            $parPeriode = $requete()
                ->with('periode')
                ->get(['periode_id', 'montant_paye'])
                ->groupBy(fn (Paiement $paiement) => $paiement->periode?->nom ?? 'Non attribuée')
                ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

            $parMois = $requete()
                ->get(['date_paiement', 'montant_paye'])
                ->groupBy(fn (Paiement $paiement) => $paiement->date_paiement?->format('Y-m') ?? '—')
                ->sortKeys()
                ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

            $parClasse = $this->recouvrementParClasse($annee->id, $periodeId);

            $paiements = $requete()
                ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire.typeFrais', 'periode'])
                ->latest('date_paiement')
                ->get();
        }

        return view('comptable.recouvrement', [
            'user' => $request->user(),
            'annee' => $annee,
            'classeId' => $classeId,
            'periodeId' => $periodeId,
            'definis' => $definis,
            'encaisse' => $encaisse,
            'reste' => max($definis - $encaisse, 0),
            'taux' => $taux,
            'parClasse' => $parClasse,
            'parPeriode' => $parPeriode,
            'parMois' => $parMois,
            'paiements' => $paiements,
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'periodes' => Periode::anneeEnCours()->orderBy('id')->get(),
        ]);
    }

    /**
     * Montants attendus et encaissés, classe par classe (période optionnelle).
     */
    private function recouvrementParClasse(int $anneeId, $periodeId = null)
    {
        $definis = FraisScolaire::query()
            ->anneeEnCours()
            ->selectRaw('classe_id, SUM(montant_exige) as total')
            ->groupBy('classe_id')
            ->pluck('total', 'classe_id');

        $encaisses = Paiement::query()
            ->join('inscriptions', 'inscriptions.id', '=', 'paiements.inscription_id')
            ->where('inscriptions.annee_scolaire_id', $anneeId)
            ->where('paiements.statut', 'actif')
            ->when($periodeId, fn ($query) => $query->where('paiements.periode_id', $periodeId))
            ->selectRaw('inscriptions.classe_id, SUM(paiements.montant_paye) as total')
            ->groupBy('inscriptions.classe_id')
            ->pluck('total', 'inscriptions.classe_id');

        return Classe::query()
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get()
            ->map(fn (Classe $classe) => (object) [
                'nom' => $classe->nom,
                'defini' => (float) ($definis[$classe->id] ?? 0),
                'encaisse' => (float) ($encaisses[$classe->id] ?? 0),
            ])
            ->filter(fn (object $ligne) => $ligne->defini > 0 || $ligne->encaisse > 0)
            ->values();
    }
}
