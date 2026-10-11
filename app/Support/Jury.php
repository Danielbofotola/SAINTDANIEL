<?php

namespace App\Support;

use App\Models\Inscription;
use App\Models\ParametreSysteme;

/**
 * Jury de délibération : applique les critères de l'établissement à la moyenne
 * du bulletin. Il ne recalcule JAMAIS la moyenne lui-même — il consomme
 * `BulletinRdc`, pour que la délibération et le bulletin restent d'accord.
 */
class Jury
{
    public function __construct(private readonly ParametreSysteme $regles)
    {
    }

    public static function regles(): self
    {
        return new self(ParametreSysteme::current());
    }

    /**
     * Analyse d'une inscription : moyenne du bulletin, branches en échec, et
     * décision proposée par les critères.
     *
     * @return array{pourcentage: float, cours_echoues: int, branches_repechage: array<int, string>, decision_proposee: string}
     */
    public function analyser(Inscription $inscription): array
    {
        $bulletin = BulletinRdc::for($inscription)->build();

        $pourcentage = (float) $bulletin['pourcentage_general'];
        $branchesEchouees = $bulletin['branches_repechage'] ?? [];
        $coursEchoues = count($branchesEchouees);

        return [
            'pourcentage' => $pourcentage,
            'cours_echoues' => $coursEchoues,
            'branches_repechage' => $branchesEchouees,
            'decision_proposee' => $this->decider($pourcentage, $coursEchoues),
        ];
    }

    /** Décision selon les critères : seuil d'admission, de repêchage, plafond de cours en échec. */
    public function decider(float $pourcentage, int $coursEchoues): string
    {
        $admission = (float) ($this->regles->deliberation_seuil_admission ?? 50);
        $repechage = (float) ($this->regles->deliberation_seuil_repechage ?? 45);
        $plafond = $this->regles->deliberation_max_cours_echoues;

        $tropDeCoursEchoues = $plafond !== null && $coursEchoues > (int) $plafond;

        if ($pourcentage >= $admission && ! $tropDeCoursEchoues) {
            return 'admis';
        }

        return $pourcentage >= $repechage ? 'ajourne' : 'redouble';
    }

    /** Photo des critères appliqués, conservée avec la décision. */
    public function critere(): array
    {
        return [
            'seuil_admission' => (float) ($this->regles->deliberation_seuil_admission ?? 50),
            'seuil_repechage' => (float) ($this->regles->deliberation_seuil_repechage ?? 45),
            'max_cours_echoues' => $this->regles->deliberation_max_cours_echoues,
            'note_min_cours' => $this->regles->deliberation_note_min_cours,
        ];
    }
}
