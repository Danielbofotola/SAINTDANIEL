<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\Sanction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Discipline — registre des sanctions du Préfet des Études.
 *
 * Le préfet (et le poste « disciplinaire ») consigne les sanctions prononcées
 * (consigne, avertissement, exclusion temporaire ou définitive), les suit et
 * peut les lever. Les élèves désactivés sont exclus : ils ne sont plus suivis.
 */
class PrefectureDisciplineController extends Controller
{
    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();
        $classes = Classe::where('statut', 'actif')->orderBy('nom')->get();

        $classeId = $request->query('classe');
        $type = $request->query('type');
        $statut = $request->query('statut');

        $sanctions = Sanction::query()
            ->with(['eleve', 'classe', 'prononcePar'])
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->when($classeId, fn ($query) => $query->where('classe_id', $classeId))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($statut, fn ($query) => $query->where('statut', $statut))
            ->orderByDesc('date_sanction')
            ->orderByDesc('id')
            ->get();

        // Élèves proposés au formulaire : scolarisés (hors désactivés/archivés),
        // inscrits cette année, éventuellement filtrés par classe.
        $eleves = Eleve::query()
            ->scolarises()
            ->when($annee, fn ($query) => $query->whereHas('inscriptions', fn ($inscription) => $inscription
                ->where('annee_scolaire_id', $annee->id)
                ->where('statut', 'actif')
                ->when($classeId, fn ($q) => $q->where('classe_id', $classeId))))
            ->orderBy('nom')->orderBy('postnom')->orderBy('prenom')
            ->get();

        return view('prefecture.discipline', [
            'user' => $request->user(),
            'schoolYear' => $annee,
            'annee' => $annee,
            'classes' => $classes,
            'sanctions' => $sanctions,
            'eleves' => $eleves,
            'classeId' => $classeId,
            'type' => $type,
            'statut' => $statut,
            'types' => Sanction::TYPES,
            'statuts' => Sanction::STATUTS,
            'stats' => [
                'total' => $sanctions->count(),
                'en_cours' => $sanctions->where('statut', 'en_cours')->count(),
                'exclusions' => $sanctions->whereIn('type', ['exclusion_temporaire', 'exclusion_definitive'])->count(),
                'eleves' => $sanctions->pluck('eleve_id')->unique()->count(),
            ],
        ]);
    }

    /** Enregistre une sanction prononcée par le préfet. */
    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’enregistrer une sanction.');
        }

        $data = $request->validate([
            'eleve_id' => ['required', Rule::exists('eleves', 'id')],
            'type' => ['required', Rule::in(array_keys(Sanction::TYPES))],
            'motif' => ['required', 'string', 'max:2000'],
            'date_sanction' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_sanction'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ], [
            'eleve_id.required' => 'Sélectionnez un élève.',
            'type.required' => 'Précisez le type de sanction.',
            'motif.required' => 'Indiquez le motif de la sanction.',
            'date_sanction.required' => 'Indiquez la date de la sanction.',
            'date_fin.after_or_equal' => 'La date de fin ne peut précéder la date de la sanction.',
        ]);

        // Classe « photographiée » : celle de l'inscription de l'année en cours.
        $inscription = Inscription::query()
            ->where('eleve_id', $data['eleve_id'])
            ->where('annee_scolaire_id', $annee->id)
            ->latest('id')
            ->first();

        Sanction::create($data + [
            'annee_scolaire_id' => $annee->id,
            'classe_id' => $inscription?->classe_id,
            'statut' => 'en_cours',
            'prononce_par' => $request->user()->id,
        ]);

        $eleve = Eleve::find($data['eleve_id']);

        return back()->with('success', 'Sanction enregistrée pour '.trim(($eleve?->nom ?? '').' '.($eleve?->prenom ?? '')).'.');
    }

    /** Lève une sanction (purge effectuée, motif réglé…). */
    public function lever(Sanction $sanction): RedirectResponse
    {
        if ($sanction->statut !== 'levee') {
            $sanction->update(['statut' => 'levee', 'levee_le' => now()]);
        }

        return back()->with('success', 'La sanction a été levée.');
    }

    /** Annule (supprime) une sanction saisie par erreur. */
    public function destroy(Sanction $sanction): RedirectResponse
    {
        $sanction->delete();

        return back()->with('success', 'La sanction a été annulée.');
    }
}
