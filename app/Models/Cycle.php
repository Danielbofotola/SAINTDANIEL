<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    protected $fillable = ['nom', 'statut'];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    /**
     * Les inscriptions des élèves passées dans les classes de ce cycle.
     */
    public function inscriptions()
    {
        return $this->hasManyThrough(Inscription::class, Classe::class);
    }
}
