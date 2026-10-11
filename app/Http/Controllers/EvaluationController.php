<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\ParametreSysteme;
use App\Models\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $classeId = $request->query('classe_id');

        $evaluations = Evaluation::query()
            ->with(['periode', 'affectation.professeur', 'affectation.cours', 'affectation.classe'])
            ->whereHas('affectation', fn ($query) => $query->anneeEnCours())
            ->when($periodeId, fn ($query) => $query->where('periode_id', $periodeId))
            ->when($classeId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('classe_id', $classeId)))
            ->withCount('notes')
            ->orderByDesc('date_evaluation')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.evaluations.index', [
            'evaluations' => $evaluations,
            'annee' => AnneeScolaire::enCours(),
            'periodeId' => $periodeId,
            'classeId' => $classeId,
            'periodes' => Periode::anneeEnCours()->where('statut', 'actif')->orderBy('id')->get(),
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'affectations' => AffectationCours::anneeEnCours()->where('statut', 'actif')->with(['professeur', 'cours', 'classe'])->get(),
            'types' => ['interro' => 'Interrogation', 'devoir' => 'Devoir', 'examen' => 'Examen', 'autre' => 'Autre'],
            'saisieNotesActive' => $this->saisieNotesActive(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible de créer une évaluation.');
        }

        $data = $request->validate([
            'affectation_id' => ['required', Rule::exists('affectations_cours', 'id')->where('annee_scolaire_id', $annee->id)->where('statut', 'actif')],
            'periode_id' => ['required', Rule::exists('periodes', 'id')->where('annee_scolaire_id', $annee->id)->where('statut', 'actif')],
            'titre' => ['required', 'string', 'max:100'],
            'type_evaluation' => ['required', Rule::in(['interro', 'devoir', 'examen', 'autre'])],
            // Facultative : si vide, la note maximale est reprise du barème du cours.
            'max_note' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'date_evaluation' => ['required', 'date'],
        ], [
            'affectation_id.required' => 'Sélectionnez une affectation (classe — cours — professeur).',
            'periode_id.required' => 'Sélectionnez une période.',
            'titre.required' => 'Le titre de l’évaluation est obligatoire.',
            'type_evaluation.required' => 'Choisissez le type d’évaluation.',
            'max_note.min' => 'La note maximale doit être supérieure à zéro.',
            'date_evaluation.required' => 'La date de l’évaluation est obligatoire.',
        ]);

        $data['max_note'] = $this->maxNote($data);

        Evaluation::create($data + ['statut' => 'actif']);

        return redirect()->route('admin.evaluations.index')->with('success', 'L’évaluation a été créée.');
    }

    /**
     * Note maximale effective : celle saisie, sinon le barème du cours de
     * l'affectation (max examen ou travaux journaliers), sinon 20 par défaut.
     *
     * @param  array<string, mixed>  $data
     */
    private function maxNote(array $data): float
    {
        if (! empty($data['max_note'])) {
            return (float) $data['max_note'];
        }

        $affectation = AffectationCours::with('cours')->find($data['affectation_id']);

        return $affectation?->cours?->maxNotePour($data['type_evaluation']) ?? 20.0;
    }

    public function notes(Evaluation $evaluation): View|RedirectResponse
    {
        if (! $this->saisieNotesActive()) {
            return redirect()->route('admin.evaluations.index')->with('error', 'La saisie des notes est désactivée (« Commuter les modules »).');
        }

        $evaluation->load(['periode', 'affectation.cours', 'affectation.classe', 'affectation.professeur']);

        $classe = $evaluation->affectation?->classe;

        $inscriptions = $classe
            ? $classe->inscriptions()->anneeEnCours()->where('statut', 'actif')->with('eleve')->get()
            : collect();

        $notes = $evaluation->notes()->pluck('note_obtenue', 'eleve_id');
        $observations = $evaluation->notes()->pluck('observation', 'eleve_id');

        return view('admin.evaluations.notes', compact('evaluation', 'inscriptions', 'notes', 'observations'));
    }

    public function storeNotes(Request $request, Evaluation $evaluation): RedirectResponse
    {
        if (! $this->saisieNotesActive()) {
            return back()->with('error', 'La saisie des notes est désactivée (« Commuter les modules »).');
        }

        // Verrouillage de fin de période décidé par le préfet : une période
        // clôturée n'accepte plus aucune modification de notes.
        if ($evaluation->periode && $evaluation->periode->statut !== 'actif') {
            return back()->with('error', 'La période « '.$evaluation->periode->nom.' » est clôturée : la saisie des notes est verrouillée.');
        }

        $data = $request->validate([
            'notes' => ['array'],
            'notes.*' => ['nullable', 'numeric', 'min:0', 'max:'.$evaluation->max_note],
            'observations' => ['array'],
            'observations.*' => ['nullable', 'string', 'max:255'],
        ], [
            'notes.*.numeric' => 'Chaque note doit être un nombre.',
            'notes.*.min' => 'La note ne peut pas être négative.',
            'notes.*.max' => 'La note ne peut pas dépasser la note maximale ('.$evaluation->max_note.').',
        ]);

        $classe = $evaluation->affectation?->classe;
        $allowed = $classe
            ? array_map('intval', $classe->inscriptions()->anneeEnCours()->where('statut', 'actif')->pluck('eleve_id')->all())
            : [];

        foreach ($data['notes'] ?? [] as $eleveId => $value) {
            $eleveId = (int) $eleveId;

            if (! in_array($eleveId, $allowed, true)) {
                continue;
            }

            if ($value === null || $value === '') {
                Note::where('evaluation_id', $evaluation->id)->where('eleve_id', $eleveId)->delete();

                continue;
            }

            Note::updateOrCreate(
                ['evaluation_id' => $evaluation->id, 'eleve_id' => $eleveId],
                [
                    'note_obtenue' => $value,
                    'observation' => $data['observations'][$eleveId] ?? null,
                    'statut' => 'actif',
                ]
            );
        }

        return redirect()->route('admin.evaluations.notes', $evaluation)->with('success', 'Les notes ont été enregistrées.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->notes()->exists()) {
            return back()->with('error', 'Cette évaluation contient déjà des notes : supprimez-les d’abord.');
        }

        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')->with('success', 'L’évaluation a été supprimée.');
    }

    /**
     * L'accès à la saisie des notes dépend du commutateur « Commuter les modules ».
     * Si les réglages sont indisponibles (table absente), la saisie reste autorisée.
     */
    private function saisieNotesActive(): bool
    {
        try {
            return (bool) ParametreSysteme::current()->saisie_notes_active;
        } catch (\Throwable) {
            return true;
        }
    }
}
