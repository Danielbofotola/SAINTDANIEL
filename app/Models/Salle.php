<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable = ['nom', 'capacite', 'statut'];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
}
