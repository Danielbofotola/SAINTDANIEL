<?php

namespace App\Support;

use App\Models\Salle;

/**
 * Occupation d'une salle (locale) : effectif inscrit dans ses classes pour
 * l'année, comparé à la capacité. Sert à signaler les locaux qui risquent d'être
 * saturés (au-delà de 90 % de la capacité) ou qui le sont déjà.
 */
class OccupationSalles
{
    /** Seuil d'alerte : part de la capacité à partir de laquelle on prévient. */
    public const SEUIL_ALERTE = 0.9;

    /**
     * @return array{effectif: int, capacite: ?int, taux: ?float, niveau: string, libelle: string}
     */
    public static function evaluer(Salle $salle): array
    {
        // Effectif = somme des inscriptions actives de l'année dans les classes
        // rattachées à la salle (alias `effectif` posé par withCount).
        $effectif = (int) $salle->classes->sum('effectif');
        $capacite = $salle->capacite !== null ? (int) $salle->capacite : null;
        $taux = $capacite !== null && $capacite > 0 ? round($effectif / $capacite * 100, 1) : null;

        if ($capacite === null) {
            $niveau = 'inconnu';
        } elseif ($effectif > $capacite) {
            $niveau = 'saturee';
        } elseif ($taux >= self::SEUIL_ALERTE * 100) {
            $niveau = 'risque';
        } else {
            $niveau = 'ok';
        }

        return [
            'effectif' => $effectif,
            'capacite' => $capacite,
            'taux' => $taux,
            'niveau' => $niveau,
            'libelle' => match ($niveau) {
                'saturee' => 'Saturée',
                'risque' => 'Presque saturée',
                'ok' => 'Disponible',
                default => 'Capacité non définie',
            },
        ];
    }

    /** Une salle est « à risque » si elle est saturée ou proche de l'être. */
    public static function aRisque(array $evaluation): bool
    {
        return in_array($evaluation['niveau'], ['risque', 'saturee'], true);
    }
}
