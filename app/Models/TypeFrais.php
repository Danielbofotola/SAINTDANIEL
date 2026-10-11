<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeFrais extends Model
{
    protected $table = 'types_frais';

    protected $fillable = ['nom', 'description', 'est_inscription', 'statut'];

    protected function casts(): array
    {
        return ['est_inscription' => 'boolean'];
    }

    /**
     * Ce type de frais déclenche-t-il la validation de l'inscription ?
     * (explicitement coché, ou nommé « frais d'inscription »).
     */
    public function estFraisInscription(): bool
    {
        return $this->est_inscription
            || str_contains(mb_strtolower((string) $this->nom), 'inscription');
    }

    public function fraisScolaires()
    {
        return $this->hasMany(FraisScolaire::class, 'type_frais_id');
    }
}
