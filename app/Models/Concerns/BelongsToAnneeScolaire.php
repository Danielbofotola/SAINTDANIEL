<?php

namespace App\Models\Concerns;

use App\Models\AnneeScolaire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * À utiliser sur tout modèle dont la table porte une colonne `annee_scolaire_id`.
 *
 * Fournit la relation vers l'année et un scope qui restreint la requête à
 * l'année en cours (et à rien du tout s'il n'y a pas d'année en cours, pour
 * éviter de fuiter les données des années passées).
 */
trait BelongsToAnneeScolaire
{
    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function scopeAnneeEnCours(Builder $query): Builder
    {
        $anneeId = AnneeScolaire::enCours()?->id;

        return $anneeId
            ? $query->where('annee_scolaire_id', $anneeId)
            : $query->whereRaw('1 = 0');
    }
}
