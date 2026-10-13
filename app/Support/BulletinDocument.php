<?php

namespace App\Support;

use App\Models\Inscription;
use App\Models\ParametreSysteme;

/**
 * Données du bulletin annuel (norme RDC) d'une inscription : résultat de
 * `BulletinRdc` complété du classement (place) de l'élève dans sa classe.
 *
 * Partagé par le module admin et l'espace titulaire.
 */
class BulletinDocument
{
    /**
     * @return array{place: ?int, nombre_eleves: int} & array<string, mixed>
     */
    public static function donnees(Inscription $inscription): array
    {
        $inscription->load(['eleve', 'classe.cycle', 'classe.section', 'classe.option', 'anneeScolaire']);

        // Seuil de réussite lu une seule fois pour tout le classement de la classe.
        $seuilReussite = ParametreSysteme::seuilReussiteBulletin();

        $data = BulletinRdc::for($inscription, $seuilReussite)->build();

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

        return $data + [
            'place' => $position === false ? null : $position + 1,
            'nombre_eleves' => $classement->count(),
        ];
    }
}
