<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $table = 'cours';

    protected $fillable = ['code', 'intitule', 'ponderation', 'max_travaux_journaliers', 'max_examen', 'statut'];

    /**
     * Maximum total de la branche pour une période (T.J. + Examen), ou null si non défini.
     */
    public function maximumParPeriode(): ?int
    {
        if ($this->max_travaux_journaliers === null && $this->max_examen === null) {
            return null;
        }

        return (int) $this->max_travaux_journaliers + (int) $this->max_examen;
    }

    public function affectations()
    {
        return $this->hasMany(AffectationCours::class);
    }

    /**
     * Note maximale conseillée pour une évaluation de ce type, d'après le barème
     * de la branche : le maximum d'examen pour un examen, sinon les travaux
     * journaliers. Renvoie null si la branche n'a aucun barème défini.
     */
    public function maxNotePour(string $type): ?float
    {
        $max = $type === 'examen'
            ? ($this->max_examen ?? $this->max_travaux_journaliers)
            : ($this->max_travaux_journaliers ?? $this->max_examen);

        return $max !== null && (float) $max > 0 ? (float) $max : null;
    }
}
