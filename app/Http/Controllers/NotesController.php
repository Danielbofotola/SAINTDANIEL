<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\ParametreSysteme;
use App\Models\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Carnet de notes par classe : l'administrateur choisit une classe
 * (et éventuellement une période) puis saisit les notes de tous ses élèves
 * dans une grille élèves × évaluations.
 */
class NotesController extends Controller
{
    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();
        $classeId = $request->query('classe_id');
        $periodeId = $request->query('periode_id');

        $classes = Classe::where('statut', 'actif')->orderBy('nom')->get();
        $periodes = Periode::anneeEnCours()->orderBy('id')->get();

        $classe = $classeId ? $classes->firstWhere('id', (int) $classeId) : null;
        $periode = $periodeId ? $periodes->firstWhere('id', (int) $periodeId) : null;

        $inscriptions = collect();
        $evaluations = collect();
        $notes = collect();

        if ($classe && $annee) {
            $inscriptions = $classe->inscriptions()
                ->anneeEnCours()
                ->where('statut', 'actif')
                ->with('eleve')
                ->get();

            $evaluations = Evaluation::query()
                ->where('statut', 'actif')
                ->whereHas('affectation', fn ($query) => $query
                    ->where('classe_id', $classe->id)
                    ->where('annee_scolaire_id', $annee->id))
                ->when($periode, fn ($query) => $query->where('periode_id', $periode->id))
                ->with(['affectation.cours', 'periode'])
                ->orderBy('periode_id')
                ->orderBy('date_evaluation')
                ->orderBy('id')
                ->get();

            $notes = Note::query()
                ->whereIn('evaluation_id', $evaluations->pluck('id'))
                ->whereIn('eleve_id', $inscriptions->pluck('eleve_id'))
                ->where('statut', 'actif')
                ->get()
                ->groupBy('eleve_id')
                ->map(fn ($groupe) => $groupe->pluck('note_obtenue', 'evaluation_id'));
        }

        return view('admin.notes.index', [
            'annee' => $annee,
            'classes' => $classes,
            'periodes' => $periodes,
            'classe' => $classe,
            'periode' => $periode,
            'classeId' => $classeId,
            'periodeId' => $periodeId,
            'inscriptions' => $inscriptions,
            'evaluations' => $evaluations,
            'notes' => $notes,
            'saisieNotesActive' => $this->saisieNotesActive(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->saisieNotesActive()) {
            return back()->with('error', 'La saisie des notes est désactivée (« Commuter les modules »).');
        }

        $data = $request->validate([
            'classe_id' => ['required', Rule::exists('classes', 'id')],
            'periode_id' => ['nullable', Rule::exists('periodes', 'id')],
            'notes' => ['array'],
        ], [
            'classe_id.required' => 'Choisissez une classe.',
        ]);

        $annee = AnneeScolaire::enCours();
        $classe = Classe::findOrFail($data['classe_id']);

        $eleveIds = $classe->inscriptions()
            ->anneeEnCours()
            ->where('statut', 'actif')
            ->pluck('eleve_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $evaluations = Evaluation::query()
            ->where('statut', 'actif')
            ->whereHas('affectation', fn ($query) => $query
                ->where('classe_id', $classe->id)
                ->when($annee, fn ($affectation) => $affectation->where('annee_scolaire_id', $annee->id)))
            ->get()
            ->keyBy('id');

        $enregistrees = 0;
        $ignorees = 0;

        DB::transaction(function () use ($request, $evaluations, $eleveIds, &$enregistrees, &$ignorees) {
            foreach ((array) $request->input('notes', []) as $eleveId => $ligne) {
                $eleveId = (int) $eleveId;

                if (! in_array($eleveId, $eleveIds, true)) {
                    continue;
                }

                foreach ((array) $ligne as $evaluationId => $valeur) {
                    $evaluation = $evaluations->get((int) $evaluationId);

                    if (! $evaluation) {
                        continue;
                    }

                    if ($valeur === null || $valeur === '') {
                        Note::where('evaluation_id', $evaluation->id)->where('eleve_id', $eleveId)->delete();

                        continue;
                    }

                    $valeur = (float) $valeur;

                    if ($valeur < 0 || $valeur > (float) $evaluation->max_note) {
                        $ignorees++;

                        continue;
                    }

                    Note::updateOrCreate(
                        ['evaluation_id' => $evaluation->id, 'eleve_id' => $eleveId],
                        ['note_obtenue' => $valeur, 'statut' => 'actif']
                    );

                    $enregistrees++;
                }
            }
        });

        $message = $enregistrees.' note(s) enregistrée(s).';
        if ($ignorees > 0) {
            $message .= ' '.$ignorees.' note(s) ignorée(s) (hors barème).';
        }

        return redirect()
            ->route('admin.notes.index', array_filter([
                'classe_id' => $classe->id,
                'periode_id' => $data['periode_id'] ?? null,
            ]))
            ->with('success', $message);
    }

    private function saisieNotesActive(): bool
    {
        try {
            return (bool) ParametreSysteme::current()->saisie_notes_active;
        } catch (\Throwable) {
            return true;
        }
    }
}
