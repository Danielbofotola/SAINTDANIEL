<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeCompte extends Model
{
    protected $table = 'demandes_comptes';

    protected $fillable = [
        'nom', 'postnom', 'prenom', 'username', 'email', 'telephone',
        'role_souhaite', 'mot_de_passe', 'statut', 'traite_par', 'traite_le', 'motif_refus',
    ];

    protected $hidden = ['mot_de_passe'];

    protected function casts(): array
    {
        return [
            'mot_de_passe' => 'hashed',
            'traite_le' => 'datetime',
        ];
    }
}
