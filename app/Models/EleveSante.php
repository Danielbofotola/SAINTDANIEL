<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EleveSante extends Model
{
    protected $table = 'eleve_sante';

    protected $fillable = ['eleve_id', 'groupe_sanguin', 'allergies_pathologies', 'traitements_urgences', 'medecin_referent'];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}