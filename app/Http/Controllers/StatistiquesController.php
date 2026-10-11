<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Depense;
use App\Models\Ecriture;
use App\Models\Eleve;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paie;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Deux pages distinctes :
 *  - « Statistiques » : répartitions (effectifs, sexes, natures de dépense,
 *    types de frais, modes de paiement, recouvrement) et totaux ;
 *  - « Évolution » : séries mensuelles (recettes, dépenses, salaires, solde,
 *    inscriptions).
 */
class StatistiquesController extends Controller
{
    /** Page Statistiques : répartitions et totaux. */
    public function index(Request $request): View
    {
        $donnees = $this->collecter();

        return view('admin.statistiques.index', [
            'user' => $request->user(),
            'annee' => $donnees['annee'],
            'effectifs' => $donnees['effectifs'],
            'parSexe' => $donnees['parSexe'],
            'depensesParNature' => $donnees['depensesParNature'],
            'recettesParType' => $donnees['recettesParType'],
            'parMode' => $donnees['parMode'],
            'recouvrement' => $donnees['recouvrement'],
            'totaux' => $donnees['totaux'],
        ]);
    }

    /** Page Évolution : séries mensuelles. */
    public function evolution(Request $request): View
    {
        $donnees = $this->collecter();

        return view('admin.statistiques.evolution', [
            'user' => $request->user(),
            'annee' => $donnees['annee'],
            'series' => $donnees['series'],
            'totaux' => $donnees['totaux'],
        ]);
    }

    /**
     * Rassemble les données des deux pages (une seule collecte, deux vues).
     *
     * @return array<string, mixed>
     */
    private function collecter(): array
    {
        $annee = AnneeScolaire::enCours();
        $mois = $this->moisDeLAnnee($annee);

        $paiements = Paiement::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id)))
            ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire.typeFrais'])
            ->get();

        $depenses = Depense::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->with('categorie')
            ->get();

        $paies = Paie::query()
            ->where('statut', 'actif')
            ->with('salaire.beneficiaire')
            ->get();

        $inscriptions = Inscription::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->get();

        // Séries mensuelles
        $recettesParMois = $this->parMois($paiements, fn (Paiement $p) => $p->date_paiement, fn (Paiement $p) => (float) $p->montant_paye);
        $depensesParMois = $this->parMois($depenses, fn (Depense $d) => $d->date_depense, fn (Depense $d) => (float) $d->montant);
        $salairesParMois = $this->parMois($paies, fn (Paie $p) => $p->date_paiement, fn (Paie $p) => (float) $p->montant_net);
        $inscriptionsParMois = $this->parMois($inscriptions, fn (Inscription $i) => $i->date_inscription, fn () => 1.0);

        $series = [
            'labels' => array_map(fn (string $cle) => $this->libelleMois($cle), $mois),
            'recettes' => array_map(fn (string $cle) => $recettesParMois[$cle] ?? 0, $mois),
            'depenses' => array_map(fn (string $cle) => $depensesParMois[$cle] ?? 0, $mois),
            'salaires' => array_map(fn (string $cle) => $salairesParMois[$cle] ?? 0, $mois),
            'inscriptions' => array_map(fn (string $cle) => (int) ($inscriptionsParMois[$cle] ?? 0), $mois),
        ];

        $series['soldes'] = array_map(
            fn (int $index) => round($series['recettes'][$index] - $series['depenses'][$index] - $series['salaires'][$index], 2),
            array_keys($mois)
        );

        // Répartitions
        $effectifs = Classe::query()
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->withCount(['inscriptions as eleves_count' => fn ($query) => $query
                ->when($annee, fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id))
                ->where('statut', 'actif')])
            ->get()
            ->filter(fn (Classe $classe) => $classe->eleves_count > 0)
            ->values();

        $parSexe = [
            'garcons' => Eleve::query()->where('sexe', 'M')
                ->whereHas('inscriptions', fn ($query) => $query->where('statut', 'actif')->when($annee, fn ($i) => $i->where('annee_scolaire_id', $annee->id)))->count(),
            'filles' => Eleve::query()->where('sexe', 'F')
                ->whereHas('inscriptions', fn ($query) => $query->where('statut', 'actif')->when($annee, fn ($i) => $i->where('annee_scolaire_id', $annee->id)))->count(),
        ];

        $depensesParNature = $depenses
            ->groupBy(fn (Depense $d) => $d->categorie?->nom ?? 'Autre')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant'), 2))
            ->sortDesc();

        $recettesParType = $paiements
            ->groupBy(fn (Paiement $p) => $p->fraisScolaire?->typeFrais?->nom ?? 'Autre')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2))
            ->sortDesc();

        $parMode = $paiements
            ->groupBy('mode_paiement')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

        // Recouvrement : attendu vs encaissé, classe par classe
        $definis = FraisScolaire::query()
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->where('statut', 'actif')
            ->selectRaw('classe_id, SUM(montant_exige) as total')
            ->groupBy('classe_id')
            ->pluck('total', 'classe_id');

        $encaisseParClasse = $paiements
            ->groupBy(fn (Paiement $p) => $p->inscription?->classe_id)
            ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

        $recouvrement = $effectifs->map(fn (Classe $classe) => [
            'classe' => $classe->nom,
            'defini' => round((float) ($definis[$classe->id] ?? 0), 2),
            'encaisse' => round((float) ($encaisseParClasse[$classe->id] ?? 0), 2),
        ])->filter(fn (array $ligne) => $ligne['defini'] > 0 || $ligne['encaisse'] > 0)->values();

        return [
            'annee' => $annee,
            'series' => $series,
            'effectifs' => $effectifs,
            'parSexe' => $parSexe,
            'depensesParNature' => $depensesParNature,
            'recettesParType' => $recettesParType,
            'parMode' => $parMode,
            'recouvrement' => $recouvrement,
            'totaux' => [
                'recettes' => round((float) $paiements->sum('montant_paye'), 2),
                'depenses' => round((float) $depenses->sum('montant'), 2),
                'salaires' => round((float) $paies->sum('montant_net'), 2),
                'eleves' => (int) $inscriptions->count(),
                'ecritures' => Ecriture::count(),
            ],
        ];
    }

    /**
     * Liste des mois « AAAA-MM » couverts par l'année scolaire (12 derniers mois par défaut).
     *
     * @return array<int, string>
     */
    private function moisDeLAnnee(?AnneeScolaire $annee): array
    {
        $debut = ($annee?->date_debut ?? now()->subMonths(11))->copy()->startOfMonth();
        $fin = $annee?->date_fin ?? now();

        $mois = [];
        $curseur = $debut->copy();

        while ($curseur <= $fin && count($mois) < 24) {
            $mois[] = $curseur->format('Y-m');
            $curseur->addMonth();
        }

        return $mois === [] ? [now()->format('Y-m')] : $mois;
    }

    /**
     * Regroupe une collection par mois (clé « AAAA-MM »).
     */
    private function parMois($collection, callable $date, callable $valeur): array
    {
        $totaux = [];

        foreach ($collection as $element) {
            $quand = $date($element);

            if (! $quand) {
                continue;
            }

            $cle = Carbon::parse($quand)->format('Y-m');
            $totaux[$cle] = round(($totaux[$cle] ?? 0) + $valeur($element), 2);
        }

        return $totaux;
    }

    private function libelleMois(string $cle): string
    {
        return Carbon::createFromFormat('Y-m', $cle)->locale('fr')->translatedFormat('M Y');
    }
}
