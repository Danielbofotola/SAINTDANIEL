<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    protected $fillable = ['nom', 'statut', 'ordre', 'fin_type', 'fin_libelle'];

    /**
     * Types de fin de cycle :
     *  - aucune     : simple passage au cycle suivant ;
     *  - certificat : remet un certificat (ex. CEP) et le cycle continue ;
     *  - diplome    : fin du parcours, l'élève sort diplômé.
     */
    public const FINS = [
        'aucune' => 'Aucune (passe au cycle suivant)',
        'certificat' => 'Certificat de fin de cycle',
        'diplome' => 'Diplôme (fin du parcours)',
    ];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    /**
     * Cycle d'enseignement suivant dans le parcours (ordre immédiatement
     * supérieur). NULL si ce cycle est le dernier, ou si l'ordre n'est pas défini.
     */
    public function suivant(): ?self
    {
        if ($this->ordre === null) {
            return null;
        }

        return static::query()
            ->where('statut', 'actif')
            ->where('ordre', '>', $this->ordre)
            ->orderBy('ordre')
            ->first();
    }

    /** Ce cycle est-il le dernier du parcours (aucun cycle après) ? */
    public function estDernier(): bool
    {
        return $this->suivant() === null;
    }

    /** Ce cycle délivre-t-il un diplôme (fin du parcours déclarée) ? */
    public function delivreDiplome(): bool
    {
        return $this->fin_type === 'diplome';
    }

    /** Ce cycle délivre-t-il un certificat intermédiaire (ex. CEP) ? */
    public function delivreCertificat(): bool
    {
        return $this->fin_type === 'certificat';
    }

    /**
     * Les inscriptions des élèves passées dans les classes de ce cycle.
     */
    public function inscriptions()
    {
        return $this->hasManyThrough(Inscription::class, Classe::class);
    }
}
