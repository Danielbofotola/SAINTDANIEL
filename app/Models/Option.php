<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';

    // La table `options` ne porte que le nom et la section de rattachement.
    protected $fillable = ['nom', 'section_id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
}