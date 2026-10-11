<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Periode extends Model
{
    use BelongsToAnneeScolaire;

    protected $table = 'periodes';

    protected $fillable = ['annee_scolaire_id', 'nom', 'date_debut', 'date_fin', 'statut'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    /**
     * Période « en cours » pour le paiement : celle dont les dates encadrent
     * aujourd'hui, sinon la première période active de l'année. Renvoie null
     * s'il n'existe aucune période pour l'année.
     */
    public static function courante(?AnneeScolaire $annee = null): ?self
    {
        $annee ??= AnneeScolaire::enCours();

        if ($annee === null || ! Schema::hasTable('periodes')) {
            return null;
        }

        $base = static::query()
            ->where('annee_scolaire_id', $annee->id)
            ->where('statut', 'actif');

        if (Schema::hasColumn('periodes', 'date_debut') && Schema::hasColumn('periodes', 'date_fin')) {
            $aujourdhui = now()->toDateString();

            $encadrante = (clone $base)
                ->whereNotNull('date_debut')
                ->whereNotNull('date_fin')
                ->where('date_debut', '<=', $aujourdhui)
                ->where('date_fin', '>=', $aujourdhui)
                ->orderBy('date_debut')
                ->first();

            if ($encadrante) {
                return $encadrante;
            }
        }

        return $base->orderBy('id')->first();
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }
}
