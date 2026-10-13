<?php

namespace App\Support;

use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\Periode;

/**
 * Classement d'une classe pour une période : total des points, maximum possible,
 * pourcentage, mention et place de chaque élève inscrit.
 *
 * Partagé par le module admin des bulletins et par l'espace titulaire, pour que
 * les deux écrans affichent strictement le même classement.
 */
class ClassementClasse
{
    /**
     * @return array<int, array{inscription: \App\Models\Inscription, total: float, max: float, pourcentage: float, mention: string, nb_notes: int, place: int}>
     */
    public static function pour(Classe $classe, Periode $periode): array
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
            // Les élèves désactivés sont hors classement et hors bulletins.
            ->whereHas('eleve', fn ($eleve) => $eleve->visibles())
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
                'mention' => self::mention($pourcentage),
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

    /** Mention correspondant à un pourcentage général. */
    public static function mention(float $pourcentage): string
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
