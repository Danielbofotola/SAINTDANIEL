<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    // La table `sections` ne porte que le nom (les colonnes code/description/statut
    // ont été retirées par la migration align_sections_options_schema).
    protected $fillable = ['nom'];

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
}