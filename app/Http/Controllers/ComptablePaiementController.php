<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\ParametreSysteme;
use App\Models\Periode;
use App\Support\Caisse;
use App\Support\Encaisse;
use App\Support\Monnaie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Caisse du comptable : filtrage des élèves (classe + nom) puis encaissement
 * d'un paiement, avec affichage du reste à payer de l'élève.
 */
class ComptablePaiementController extends Controller
{
    private const MODES = ['especes' => 'Espèces', 'banque' => 'Banque', 'mobile_money' => 'Mobile Money'];

    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();
        $classeId = $request->query('classe_id');

        $inscriptions = $annee
            ? Inscription::query()
                ->where('annee_scolaire_id', $annee->id)
                ->whereIn('statut', ['actif', 'en_attente'])
                ->when($classeId, fn ($query) => $query->where('classe_id', $classeId))
                ->with(['eleve', 'classe'])
                ->get()
            : collect();

        $tarifs = $annee
            ? FraisScolaire::query()
                ->where('annee_scolaire_id', $annee->id)
                ->where('statut', 'actif')
                ->when($classeId, fn ($query) => $query->where('classe_id', $classeId))
                ->with(['typeFrais', 'classe'])
                ->get()
            : collect();

        $soldePaye = $annee
            ? Paiement::query()
                ->where('statut', 'actif')
                ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                ->get(['inscription_id', 'frais_scolaire_id', 'montant_paye'])
                ->groupBy(fn ($paiement) => $paiement->inscription_id.'-'.$paiement->frais_scolaire_id)
                ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2))
            : collect();

        $paiements = $annee
            ? Paiement::query()
                ->where('statut', 'actif')
                ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                ->when($classeId, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('classe_id', $classeId)))
                ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire.typeFrais'])
                ->latest('date_paiement')
                ->limit(15)
                ->get()
            : collect();

        $classes = Classe::where('statut', 'actif')->orderBy('nom')->get();

        return view('comptable.paiements', [
            'user' => $request->user(),
            'annee' => $annee,
            'classeId' => $classeId,
            'classes' => $classes,
            'inscriptions' => $inscriptions,
            'tarifs' => $tarifs,
            'soldePaye' => $soldePaye,
            'montantsFrais' => $tarifs->pluck('montant_exige', 'id'),
            'classesParInscription' => $inscriptions->pluck('classe_id', 'id'),
            'classesParFrais' => $tarifs->pluck('classe_id', 'id'),
            'nomsClasses' => $classes->pluck('nom', 'id'),
            'devises' => Monnaie::DEVISES,
            'tauxParDefaut' => ParametreSysteme::tauxChange(),
            'tarifsDisponibles' => $tarifs->map(fn ($tarif) => [
                'id' => $tarif->id,
                'classe' => $tarif->classe_id,
                'libelle' => ($tarif->typeFrais?->nom ?? 'Tarif').' — '.($tarif->classe?->nom ?? '—')
                    .' ('.Monnaie::format($tarif->montant_exige, $tarif->devise).')',
                'devise' => Monnaie::normaliser($tarif->devise),
            ])->values(),
            'paiements' => $paiements,
            'modes' => self::MODES,
            'periodes' => Periode::anneeEnCours()->orderBy('id')->get(),
            'periodeCourante' => Periode::courante($annee),
            'encaisse' => $annee
                ? (float) Paiement::query()->where('statut', 'actif')
                    ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                    ->sum('montant_paye')
                : 0.0,
            'totauxParDevise' => Encaisse::parDevise($annee),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’encaisser un paiement.');
        }

        $data = $request->validate([
            'inscription_id' => ['required', Rule::exists('inscriptions', 'id')->where(fn ($query) => $query->where('annee_scolaire_id', $annee->id)->whereIn('statut', ['actif', 'en_attente']))],
            'frais_scolaire_id' => ['required', Rule::exists('frais_scolaires', 'id')->where('annee_scolaire_id', $annee->id)->where('statut', 'actif')],
            'periode_id' => ['nullable', Rule::exists('periodes', 'id')->where('annee_scolaire_id', $annee->id)],
            'montant_paye' => ['required', 'numeric', 'min:0.01'],
            'devise' => ['nullable', Rule::in(array_keys(Monnaie::DEVISES))],
            'taux' => ['nullable', 'numeric', 'min:0'],
            'mode_paiement' => ['required', Rule::in(array_keys(self::MODES))],
            'reference_transaction' => ['nullable', 'string', 'max:100'],
            'date_paiement' => ['nullable', 'date'],
            'classe_id' => ['nullable', Rule::exists('classes', 'id')],
        ], [
            'inscription_id.required' => 'Sélectionnez un élève.',
            'frais_scolaire_id.required' => 'Sélectionnez un tarif.',
            'montant_paye.required' => 'Le montant encaissé est obligatoire.',
            'montant_paye.min' => 'Le montant doit être supérieur à zéro.',
        ]);

        // Période automatique : celle en cours si le caissier n'en choisit pas.
        $data['periode_id'] = ($data['periode_id'] ?? null) ?: Periode::courante($annee)?->id;

        try {
            $paiement = Caisse::enregistrer($annee, $data);
        } catch (RuntimeException) {
            return back()->withInput()->with('error', 'Ce tarif ne correspond pas à la classe de l’élève.');
        }

        $message = 'Paiement encaissé — reçu '.$paiement->num_recu.'.';
        if ($paiement->fraisScolaire?->typeFrais?->estFraisInscription()
            && Inscription::whereKey($data['inscription_id'])->value('statut') === Inscription::STATUT_ACTIF) {
            $message .= ' L’inscription de l’élève a été validée automatiquement (frais d’inscription réglé).';
        }

        return redirect()
            ->route('comptable.paiements.index', array_filter(['classe_id' => $data['classe_id'] ?? null]))
            ->with('success', $message);
    }
}
