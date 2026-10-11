<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Présence d'un élève à une séance (un cours affecté, à une date donnée).
 */
class Presence extends Model
{
    public const STATUTS = [
        'present' => 'Présent',
        'absent' => 'Absent',
        'retard' => 'En retard',
        'excuse' => 'Excusé',
    ];

    protected $table = 'presences';

    protected $fillable = ['affectation_id', 'eleve_id', 'date_seance', 'statut_presence', 'motif', 'enregistre_par'];

    // date_seance reste une chaîne « AAAA-MM-JJ » : la recherche/saisie d'un
    // appel doit comparer exactement la même valeur qu'à l'enregistrement.

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}
