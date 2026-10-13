<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Certificat ou diplôme délivré à un élève à la fin d'un cycle.
 */
class Certification extends Model
{
    protected $fillable = ['eleve_id', 'cycle_id', 'annee_scolaire_id', 'libelle', 'date_obtention'];

    protected function casts(): array
    {
        return ['date_obtention' => 'date'];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}
