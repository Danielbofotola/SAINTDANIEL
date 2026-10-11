<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Note;
use App\Support\EspaceProfesseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Espace enseignant : évaluations et saisie des notes.
 */
class ProfesseurEvaluationController extends Controller
{
    public const TYPES = ['interro' => 'Interrogation', 'devoir' => 'Devoir', 'examen' => 'Examen', 'autre' => 'Évaluation'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = EspaceProfesseur::annee();
        $affectations = EspaceProfesseur::affectations($professor?->id, $schoolYear?->id);
        $evaluations = EspaceProfesseur::evaluations($affectations);

        $effectifs = DB::table('inscriptions')
            ->where('annee_scolaire_id', $schoolYear?->id)
            ->where('statut', 'actif')
            ->whereIn('classe_id', $affectations->pluck('classe_id')->unique())
            ->selectRaw('classe_id, COUNT(DISTINCT eleve_id) as total')
            ->groupBy('classe_id')
            ->pluck('total', 'classe_id');

        return view('professeur.evaluations', [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'affectations' => $affectations,
            'evaluations' => $evaluations,
            'aVenir' => $evaluations->filter(fn ($e) => $e->date_evaluation >= today()->toDateString())->values(),
            'passees' => $evaluations->filter(fn ($e) => $e->date_evaluation < today()->toDateString())->values(),
            'periodes' => $schoolYear
                ? DB::table('periodes')->where('annee_scolaire_id', $schoolYear->id)->where('statut', 'actif')->orderBy('nom')->get()
                : collect(),
            'effectifs' => $effectifs,
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $professeurId = EspaceProfesseur::professeurId($request->user());
        $schoolYear = EspaceProfesseur::annee();

        if (! $schoolYear) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible de créer une évaluation.');
        }

        $affectations = EspaceProfesseur::affectations($professeurId, $schoolYear->id);

        $data = $request->validate([
            'affectation_id' => ['required', Rule::in($affectations->pluck('id')->all())],
            'periode_id' => ['required', Rule::exists('periodes', 'id')->where('annee_scolaire_id', $schoolYear->id)],
            'titre' => ['required', 'string', 'max:100'],
            'type_evaluation' => ['required', Rule::in(array_keys(self::TYPES))],
            // Facultative : si vide, la note maximale est reprise du barème du cours.
            'max_note' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'date_evaluation' => ['required', 'date'],
        ], [
            'affectation_id.required' => 'Choisissez le cours concerné.',
            'affectation_id.in' => 'Ce cours ne vous est pas affecté.',
            'periode_id.required' => 'Choisissez la période.',
            'titre.required' => 'Le titre de l’évaluation est obligatoire.',
            'max_note.min' => 'La note maximale doit être supérieure à zéro.',
        ]);

        if (empty($data['max_note'])) {
            $affectation = \App\Models\AffectationCours::with('cours')->find($data['affectation_id']);
            $data['max_note'] = $affectation?->cours?->maxNotePour($data['type_evaluation']) ?? 20.0;
        }

        $evaluation = Evaluation::create($data + ['statut' => 'actif']);

        return redirect()
            ->route('professeur.notes', $evaluation)
            ->with('success', 'Évaluation « '.$data['titre'].' » créée : vous pouvez saisir les notes.');
    }

    public function notes(Request $request, Evaluation $evaluation): View
    {
        $contexte = $this->contexteEvaluation($request, $evaluation);

        $eleves = EspaceProfesseur::eleveParClasse(
            EspaceProfesseur::eleves($contexte['affectations'], $contexte['schoolYear']?->id),
            (int) $evaluation->affectation->classe_id
        );

        $notes = Note::query()
            ->where('evaluation_id', $evaluation->id)
            ->get()
            ->keyBy('eleve_id');

        $saisies = $notes->count();

        return view('professeur.notes', $contexte + [
            'evaluation' => $evaluation,
            'detail' => $contexte['affectations']->firstWhere('id', $evaluation->affectation_id),
            'eleves' => $eleves,
            'notes' => $notes,
            'saisies' => $saisies,
            'restants' => max($eleves->count() - $saisies, 0),
            'moyenne' => $notes->count() > 0 ? round((float) $notes->avg('note_obtenue'), 2) : null,
        ]);
    }

    public function storeNotes(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->contexteEvaluation($request, $evaluation);

        $max = (float) $evaluation->max_note;

        $data = $request->validate([
            'notes' => ['required', 'array'],
            'notes.*' => ['nullable', 'numeric', 'min:0', 'max:'.$max],
            'observations' => ['nullable', 'array'],
            'observations.*' => ['nullable', 'string', 'max:200'],
        ], [
            'notes.*.max' => 'Aucune note ne peut dépasser le maximum de '.$max.' pour cette évaluation.',
            'notes.*.numeric' => 'Les notes doivent être des nombres.',
        ]);

        $enregistrees = 0;

        // Verrouillage de fin de période (décision du préfet) : plus aucune
        // modification de notes n'est acceptée sur une période clôturée.
        if ($evaluation->periode && $evaluation->periode->statut !== 'actif') {
            return back()->with('error', 'La période « '.$evaluation->periode->nom.' » est clôturée : la saisie des notes est verrouillée.');
        }

        DB::transaction(function () use ($data, $evaluation, $request, &$enregistrees) {
            foreach ($data['notes'] as $eleveId => $valeur) {
                $observation = $data['observations'][$eleveId] ?? null;

                if ($valeur === null || $valeur === '') {
                    // Note effacée : on désactive la ligne si elle existait.
                    Note::query()
                        ->where('evaluation_id', $evaluation->id)
                        ->where('eleve_id', $eleveId)
                        ->update(['statut' => 'inactif', 'observation' => $observation]);
                    continue;
                }

                Note::updateOrCreate(
                    ['evaluation_id' => $evaluation->id, 'eleve_id' => $eleveId],
                    ['note_obtenue' => $valeur, 'observation' => $observation, 'statut' => 'actif']
                );

                $enregistrees++;
            }
        });

        $commentaire = $enregistrees === 0
            ? 'Aucune note saisie : vérifiez les valeurs.'
            : $enregistrees.' note(s) enregistrée(s) pour « '.$evaluation->titre.' ».';

        return back()->with('success', $commentaire);
    }

    /**
     * Vérifie que l'évaluation appartient à une affectation du professeur connecté.
     *
     * @return array<string, mixed>
     */
    private function contexteEvaluation(Request $request, Evaluation $evaluation): array
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = EspaceProfesseur::annee();
        $affectations = EspaceProfesseur::affectations($professor?->id, $schoolYear?->id);

        abort_unless($affectations->contains('id', $evaluation->affectation_id), 403, 'Cette évaluation ne concerne pas vos cours.');

        return [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'affectations' => $affectations,
        ];
    }
}
