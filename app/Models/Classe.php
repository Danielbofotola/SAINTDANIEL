<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $fillable = ['cycle_id', 'salle_id', 'nom', 'ordre', 'classe_suivante_id', 'section_id', 'option_id', 'statut'];

    /**
     * Classe de destination en fin d'année : le passage explicite configuré
     * (`classe_suivante_id`) s'il existe — il peut franchir un cycle —, sinon la
     * classe d'ordre immédiatement supérieur dans le même cycle.
     */
    public function suivante(): ?self
    {
        if ($this->classe_suivante_id !== null) {
            return $this->classeSuivante()->first();
        }

        if ($this->ordre === null) {
            return null;
        }

        return static::query()
            ->where('cycle_id', $this->cycle_id)
            ->where('statut', 'actif')
            ->where('ordre', $this->ordre + 1)
            ->first();
    }

    /** Passage configuré explicitement vers une autre classe (même cycle ou non). */
    public function classeSuivante()
    {
        return $this->belongsTo(Classe::class, 'classe_suivante_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
