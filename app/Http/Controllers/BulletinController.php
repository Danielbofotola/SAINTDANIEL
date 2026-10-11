<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\ParametreSysteme;
use App\Models\Periode;
use App\Support\BulletinRdc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $classeId = $request->query('classe_id');

        $annee = AnneeScolaire::enCours();
        $periodes = Periode::anneeEnCours()->orderBy('id')->get();
        $classes = Classe::where('statut', 'actif')->orderBy('nom')->get();

        $periode = null;
        $classe = null;
        $rows = collect();

        if ($annee && $periodeId && $classeId) {
            $periode = $periodes->firstWhere('id', (int) $periodeId);
            $classe = $classes->firstWhere('id', (int) $classeId);

            if ($periode && $classe) {
                $rows = collect($this->computeForClasse($classe, $periode))->map(function (array $row) use ($periode) {
                    $row['bulletin'] = Bulletin::query()
                        ->where('periode_id', $periode->id)
                        ->where('inscription_id', $row['inscription']->id)
                        ->first();

                    return $row;
                });
            }
        }

        return view('admin.bulletins.index', compact('annee', 'periodes', 'classes', 'periode', 'classe', 'rows', 'periodeId', 'classeId'));
    }

    public function document(Inscription $inscription): View
    {
        $inscription->load(['eleve', 'classe.cycle', 'classe.section', 'classe.option', 'anneeScolaire']);

        // Seuil de réussite configuré dans les paramètres : lu une seule fois pour
        // tout le classement de la classe (évite une requête par élève).
        $seuilReussite = ParametreSysteme::seuilReussiteBulletin();

        $data = BulletinRdc::for($inscription, $seuilReussite)->build();

        // Classement de l'élève dans sa classe par pourcentage général.
        $classement = Inscription::query()
            ->where('classe_id', $inscription->classe_id)
            ->where('annee_scolaire_id', $inscription->annee_scolaire_id)
            ->where('statut', 'actif')
            ->get()
            ->map(fn (Inscription $item) => [
                'id' => $item->id,
                'pourcentage' => BulletinRdc::for($item, $seuilReussite)->build()['pourcentage_general'],
            ])
            ->sortByDesc('pourcentage')
            ->values();

        $position = $classement->search(fn (array $row) => $row['id'] === $inscription->id);

        return view('admin.bulletins.document', $data + [
            'place' => $position === false ? null : $position + 1,
            'nombre_eleves' => $classement->count(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', Rule::exists('periodes', 'id')],
            'classe_id' => ['required', Rule::exists('classes', 'id')],
        ], [
            'periode_id.required' => 'Choisissez une période.',
            'classe_id.required' => 'Choisissez une classe.',
        ]);

        $periode = Periode::anneeEnCours()->find($data['periode_id']);
        $classe = Classe::find($data['classe_id']);

        if (! $periode || ! $classe) {
            return back()->withInput()->with('error', 'Période ou classe introuvable pour l’année en cours.');
        }

        $rows = $this->computeForClasse($classe, $periode);

        if ($rows === []) {
            return back()->with('error', 'Aucun élève inscrit dans cette classe pour l’année en cours.');
        }

        DB::transaction(function () use ($rows, $periode) {
            foreach ($rows as $row) {
                Bulletin::updateOrCreate(
                    ['inscription_id' => $row['inscription']->id, 'periode_id' => $periode->id],
                    [
                        'total_points' => $row['total'],
                        'max_possible' => $row['max'],
                        'pourcentage' => $row['pourcentage'],
                        'place_classe' => $row['place'],
                        'mention' => $row['mention'],
                        'statut' => 'actif',
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.bulletins.index', ['periode_id' => $periode->id, 'classe_id' => $classe->id])
            ->with('success', 'Les bulletins de la classe ont été générés.');
    }

    /**
     * Calcule, pour chaque élève inscrit de la classe, le total des points obtenus,
     * le maximum possible sur les évaluations notées de la période, le pourcentage,
     * la place et la mention.
     *
     * @return array<int, array{inscription: \App\Models\Inscription, total: float, max: float, pourcentage: float, mention: string, nb_notes: int, place: int}>
     */
    private function computeForClasse(Classe $classe, Periode $periode): array
    {
        $evaluations = Evaluation::query()
            ->where('periode_id', $periode->id)
            ->where('statut', 'actif')
            ->whereHas('affectation', fn ($query) => $query->where('classe_id', $classe->id)->anneeEnCours())
            ->get(['id', 'max_note']);

        $maxParEvaluation = $evaluations->pluck('max_note', 'id');

        $inscriptions = $classe->inscriptions()
            ->anneeEnCours()
            ->where('statut', 'actif')
            ->with('eleve')
            ->get();

        $notes = Note::query()
            ->whereIn('evaluation_id', $evaluations->pluck('id'))
            ->whereIn('eleve_id', $inscriptions->pluck('eleve_id'))
            ->where('statut', 'actif')
            ->get(['evaluation_id', 'eleve_id', 'note_obtenue'])
            ->groupBy('eleve_id');

        $rows = [];
        foreach ($inscriptions as $inscription) {
            $eleveNotes = $notes->get($inscription->eleve_id, collect());
            $total = round((float) $eleveNotes->sum('note_obtenue'), 2);
            $max = round((float) $eleveNotes->sum(fn ($note) => (float) ($maxParEvaluation[$note->evaluation_id] ?? 0)), 2);
            $pourcentage = $max > 0 ? round($total / $max * 100, 2) : 0.0;

            $rows[] = [
                'inscription' => $inscription,
                'total' => $total,
                'max' => $max,
                'pourcentage' => $pourcentage,
                'mention' => $this->mention($pourcentage),
                'nb_notes' => $eleveNotes->count(),
            ];
        }

        usort($rows, fn (array $a, array $b) => $b['pourcentage'] <=> $a['pourcentage']);

        foreach ($rows as $index => &$row) {
            $row['place'] = $index + 1;
        }
        unset($row);

        return $rows;
    }

    private function mention(float $pourcentage): string
    {
        return match (true) {
            $pourcentage >= 80 => 'Distinction',
            $pourcentage >= 70 => 'Satisfaction',
            $pourcentage >= 60 => 'Passable',
            $pourcentage >= 50 => 'Faible',
            default => 'Insuffisant',
        };
    }
}
