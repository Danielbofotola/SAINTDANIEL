<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use BelongsToAnneeScolaire;

    protected $table = 'depenses';

    protected $fillable = ['annee_scolaire_id', 'category_id', 'motif', 'montant', 'date_depense', 'piece_justificative', 'statut'];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_depense' => 'date',
        ];
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieDepense::class, 'category_id');
    }
}
