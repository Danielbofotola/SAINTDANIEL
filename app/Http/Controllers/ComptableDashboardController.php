<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Support\Encaisse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ComptableDashboardController extends Controller
{
    /**
     * Tableau de bord financier de l'année scolaire en cours.
     */
    public function index(Request $request): View
    {
        $schoolYear = AnneeScolaire::enCours();

        $fraisDefinis = 0.0;
        $encaisse = 0.0;
        $nbPaiements = 0;
        $elevesInscrits = 0;
        $parClasse = collect();
        $derniersPaiements = collect();

        if ($schoolYear) {
            $fraisDefinis = (float) FraisScolaire::query()->anneeEnCours()->sum('montant_exige');
            $encaisse = (float) $this->paiementsDeLAnnee($schoolYear->id)->sum('montant_paye');
            $nbPaiements = $this->paiementsDeLAnnee($schoolYear->id)->count();
            $elevesInscrits = Inscription::query()->anneeEnCours()->where('statut', 'actif')->distinct()->count('eleve_id');
            $parClasse = $this->recouvrementParClasse($schoolYear->id);
            $derniersPaiements = $this->paiementsDeLAnnee($schoolYear->id)
                ->with(['inscription.eleve', 'inscription.classe'])
                ->latest('date_paiement')
                ->limit(8)
                ->get();
        }

        $reste = max($fraisDefinis - $encaisse, 0);
        $taux = $fraisDefinis > 0 ? round($encaisse / $fraisDefinis * 100, 1) : 0.0;

        return view('comptable.dashboard', [
            'user' => $request->user(),
            'schoolYear' => $schoolYear,
            'fraisDefinis' => $fraisDefinis,
            'encaisse' => $encaisse,
            'reste' => $reste,
            'taux' => $taux,
            'nbPaiements' => $nbPaiements,
            'elevesInscrits' => $elevesInscrits,
            'encaisseParDevise' => $schoolYear ? Encaisse::parDevise($schoolYear) : [],
            'parClasse' => $parClasse,
            'derniersPaiements' => $derniersPaiements,
        ]);
    }

    /**
     * Les paiements rattachés aux inscriptions de l'année donnée.
     */
    private function paiementsDeLAnnee(int $anneeId)
    {
        return Paiement::query()->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $anneeId));
    }

    /**
     * Montants attendus et encaissés, classe par classe.
     */
    private function recouvrementParClasse(int $anneeId): Collection
    {
        $definis = FraisScolaire::query()
            ->anneeEnCours()
            ->selectRaw('classe_id, SUM(montant_exige) as total')
            ->groupBy('classe_id')
            ->pluck('total', 'classe_id');

        $encaisses = Paiement::query()
            ->join('inscriptions', 'inscriptions.id', '=', 'paiements.inscription_id')
            ->where('inscriptions.annee_scolaire_id', $anneeId)
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
