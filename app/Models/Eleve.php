<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'matricule',
        'postnom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'tuteur_id',
        'photo',
        'statut',
    ];

    protected function casts(): array
    {
        return ['date_naissance' => 'date'];
    }

    public function tuteur()
    {
        return $this->belongsTo(Tuteur::class);
    }

    /**
     * Comptes parents rattachés à cet élève.
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'eleve_user')->withTimestamps();
    }

    /**
     * Élèves visibles dans l'application : tout sauf les élèves désactivés.
     * Un élève « inactif » disparaît des listes, des effectifs et des écrans de suivi.
     */
    public function scopeVisibles(Builder $query): Builder
    {
        return $query->where('statut', '!=', 'inactif');
    }

    /**
     * Limite la requête aux élèves inscrits (inscription active) pour l'année en cours.
     * Les élèves désactivés sont exclus : l'application ne les affiche plus.
     */
    public function scopeInscritAnneeEnCours(Builder $query): Builder
    {
        return $query->visibles()->whereHas('inscriptions', fn (Builder $inscription) => $inscription
            ->anneeEnCours()
            ->where('statut', 'actif'));
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function sante()
    {
        return $this->hasOne(EleveSante::class);
    }

    public function documents()
    {
        return $this->hasMany(DocumentEleve::class);
    }
}
