<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    protected $table = 'emplois_du_temps';

    protected $fillable = ['affectation_id', 'salle_id', 'jour', 'heure_debut', 'heure_fin', 'statut'];

    public function affectation()
    {
        return $this->belongsTo(AffectationCours::class, 'affectation_id');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}
