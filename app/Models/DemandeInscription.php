<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeInscription extends Model
{
    protected $table = 'demandes_inscription';

    protected $fillable = [
        'nom', 'postnom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance',
        'classe_id', 'annee_scolaire_id', 'nom_mere', 'telephone_mere', 'email_mere',
        'adresse_mere', 'nom_pere', 'telephone_pere', 'email_pere', 'statut',
        'traite_par', 'traite_le', 'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'traite_le' => 'datetime',
        ];
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}
