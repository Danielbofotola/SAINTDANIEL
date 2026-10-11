<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;

class FraisScolaire extends Model
{
    use BelongsToAnneeScolaire;

    protected $table = 'frais_scolaires';

    protected $fillable = ['type_frais_id', 'classe_id', 'annee_scolaire_id', 'montant_exige', 'devise', 'date_echeance', 'statut'];

    protected function casts(): array
    {
        return [
            'montant_exige' => 'decimal:2',
            'date_echeance' => 'date',
        ];
    }

    public function typeFrais()
    {
        return $this->belongsTo(TypeFrais::class, 'type_frais_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
