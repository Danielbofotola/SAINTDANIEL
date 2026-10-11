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

class PaiementController extends Controller
{
    private const MODES = ['especes' => 'Espèces', 'banque' => 'Banque', 'mobile_money' => 'Mobile Money'];

    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();
        $classeId = $request->query('classe_id');
        $periodeId = $request->query('periode_id');
        $search = trim((string) $request->query('q', ''));

        $paiements = Paiement::query()
            ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire.typeFrais', 'fraisScolaire.classe'])
            ->whereHas('inscription', fn ($query) => $query->when($annee, fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id)))
            ->when($classeId, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('classe_id', $classeId)))
            ->when($periodeId, fn ($query) => $query->where('periode_id', $periodeId))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('num_recu', 'like', "%{$search}%")
                    ->orWhereHas('inscription.eleve', fn ($eleve) => $eleve
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('postnom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                        ->orWhere('matricule', 'like', "%{$search}%"));
            }))
            ->latest('date_paiement')
            ->paginate(25)
            ->withQueryString();

        $tarifs = $annee
            ? FraisScolaire::query()->where('annee_scolaire_id', $annee->id)->where('statut', 'actif')->with(['typeFrais', 'classe'])->get()
            : collect();

        // Montant déjà réglé par couple (inscription, tarif) pour l'année en cours :
        // sert à afficher le reste à payer lorsque le règlement se fait par tranches.
        $soldePaye = $annee
            ? Paiement::query()
                ->where('statut', 'actif')
                ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                ->get(['inscription_id', 'frais_scolaire_id', 'montant_paye'])
                ->groupBy(fn ($paiement) => $paiement->inscription_id.'-'.$paiement->frais_scolaire_id)
                ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2))
            : collect();

        return view('admin.paiements.index', [
            'paiements' => $paiements,
            'annee' => $annee,
            'classeId' => $classeId,
            'periodeId' => $periodeId,
            'search' => $search,
            'modes' => self::MODES,
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'periodes' => Periode::anneeEnCours()->orderBy('id')->get(),
            'periodeCourante' => Periode::courante($annee),
            'inscriptions' => $annee
                ? Inscription::query()->where('annee_scolaire_id', $annee->id)->whereIn('statut', ['actif', 'en_attente'])->with(['eleve', 'classe'])->get()
                : collect(),
            'tarifs' => $tarifs,
            'soldePaye' => $soldePaye,
            'montantsFrais' => $tarifs->pluck('montant_exige', 'id'),
            'totalEncaisse' => $annee
                ? (float) Paiement::query()->where('statut', 'actif')
                    ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $annee->id))
                    ->sum('montant_paye')
                : 0.0,
            'devises' => Monnaie::DEVISES,
            'tauxParDefaut' => ParametreSysteme::tauxChange(),
            'totauxParDevise' => Encaisse::parDevise($annee),
            'deviseParFrais' => $tarifs->mapWithKeys(fn ($tarif) => [$tarif->id => Monnaie::normaliser($tarif->devise)]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’enregistrer un paiement.');
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
        ], [
            'inscription_id.required' => 'Sélectionnez un élève inscrit.',
            'frais_scolaire_id.required' => 'Sélectionnez un tarif.',
            'montant_paye.required' => 'Le montant payé est obligatoire.',
            'montant_paye.min' => 'Le montant payé doit être supérieur à zéro.',
        ]);

        // Période automatique : celle en cours si l'utilisateur n'en choisit pas.
        $data['periode_id'] = ($data['periode_id'] ?? null) ?: Periode::courante($annee)?->id;

        try {
            $paiement = Caisse::enregistrer($annee, $data);
        } catch (\RuntimeException) {
            return back()->withInput()->with('error', 'Ce tarif ne correspond pas à la classe de l’élève.');
        }

        $message = 'Le paiement a été enregistré sous le reçu '.$paiement->num_recu.'.';
        if ($paiement->fraisScolaire?->typeFrais?->estFraisInscription()
            && Inscription::whereKey($data['inscription_id'])->value('statut') === Inscription::STATUT_ACTIF) {
            $message .= ' L’inscription de l’élève a été validée automatiquement (frais d’inscription réglé).';
        }

        return redirect()->route('admin.paiements.index')->with('success', $message);
    }

    public function destroy(Paiement $paiement): RedirectResponse
    {
        $paiement->delete();

        return redirect()->route('admin.paiements.index')->with('success', 'Le paiement a été supprimé.');
    }
}
