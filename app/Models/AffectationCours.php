<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;

class AffectationCours extends Model
{
    use BelongsToAnneeScolaire;

    protected $table = 'affectations_cours';

    protected $fillable = ['professeur_id', 'cours_id', 'classe_id', 'annee_scolaire_id', 'statut'];

    public function professeur()
    {
        return $this->belongsTo(Professeurs::class, 'professeur_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function emploisDuTemps()
    {
        return $this->hasMany(EmploiDuTemps::class, 'affectation_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'affectation_id');
    }
}
