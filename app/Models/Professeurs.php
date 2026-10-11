<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professeurs extends Model
{
    protected $fillable = ['matricule', 'nom', 'postnom', 'prenom', 'sexe', 'specialite', 'telephone', 'email', 'photo', 'statut'];

    public function user()
    {
        return $this->hasOne(User::class, 'professeur_id');
    }

    public function affectationsCours()
    {
        return $this->hasMany(AffectationCours::class, 'professeur_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentProfesseur::class, 'professeur_id');
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom.' '.$this->postnom.' '.$this->prenom);
    }
}
