<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tuteur extends Model
{
    protected $fillable = ['nom_pere', 'profession_pere', 'telephone_pere', 'email_pere', 'adresse_pere', 'nom_mere', 'profession_mere', 'telephone_mere', 'email_mere', 'adresse_mere', 'statut'];
}
