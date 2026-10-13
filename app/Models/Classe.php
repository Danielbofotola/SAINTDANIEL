<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Classe extends Model
{
    protected $fillable = ['cycle_id', 'salle_id', 'nom', 'ordre', 'classe_suivante_id', 'section_id', 'option_id', 'statut'];

    /**
     * Classe de destination en fin d'année : le passage explicite configuré
     * (`classe_suivante_id`) s'il existe — il peut franchir un cycle —, sinon la
     * classe d'ordre immédiatement supérieur dans le même cycle.
     */
    public function suivante(): ?self
    {
        if ($this->classe_suivante_id !== null) {
            return $this->classeSuivante()->first();
        }

        if ($this->ordre === null) {
            return null;
        }

        return static::query()
            ->where('cycle_id', $this->cycle_id)
            ->where('statut', 'actif')
            ->where('ordre', $this->ordre + 1)
            ->first();
    }

    /** Passage configuré explicitement vers une autre classe (même cycle ou non). */
    public function classeSuivante()
    {
        return $this->belongsTo(Classe::class, 'classe_suivante_id');
    }

    /**
     * Destinations explicitement configurables en fin d'année : une classe
     * « portail » peut mener vers plusieurs classes d'accueil (ex. les 1res
     * humanités, une par section/option). Le passage est alors un choix par élève.
     */
    public function destinations()
    {
        return $this->belongsToMany(Classe::class, 'classe_destinations', 'classe_id', 'destination_id')
            ->withTimestamps();
    }

    /**
     * Classes d'accueil possibles en fin d'année, par ordre de priorité :
     *  1. les destinations explicites si configurées (peut franchir un cycle,
     *     et peut être multiple → le choix de section/option devient obligatoire) ;
     *  2. sinon la classe suivante déduite (`classe_suivante_id` ou niveau + 1) ;
     *  3. en fin de cycle, le 1er niveau du cycle suivant (selon `Cycle.ordre`).
     *
     * @return Collection<int, self>
     */
    public function candidats(): Collection
    {
        $explicites = $this->destinations()
            ->where('classes.statut', 'actif')
            ->orderBy('classes.nom')
            ->get();

        if ($explicites->isNotEmpty()) {
            return $explicites;
        }

        $suivante = $this->suivante();

        if ($suivante) {
            return collect([$suivante]);
        }

        // Sans ordre de classe, on ne peut pas savoir qu'elle clôt son cycle.
        if ($this->ordre === null) {
            return collect();
        }

        $cycleSuivant = $this->cycle?->suivant();

        if (! $cycleSuivant) {
            return collect();
        }

        // On ne retient que le 1er niveau du cycle suivant (les 1res humanités,
        // par exemple), pas tous ses niveaux.
        $classes = $cycleSuivant->classes()->where('statut', 'actif');
        $premier = (clone $classes)->min('ordre');

        if ($premier !== null) {
            $classes->where('ordre', $premier);
        }

        return $classes->orderBy('nom')->get();
    }

    /**
     * Classe terminale : plus aucune classe d'accueil. Si son cycle est le
     * dernier du parcours, ses admis sont diplômés ; sinon ils passent au cycle
     * suivant.
     */
    public function estTerminale(): bool
    {
        return $this->ordre !== null && $this->candidats()->isEmpty();
    }

    /**
     * La classe clôt-elle son cycle (aucun niveau supérieur dans le même cycle) ?
     * C'est au terme d'un cycle que se délivre un certificat (ex. CEP) ou un diplôme.
     */
    public function clotUnCycle(): bool
    {
        if ($this->ordre === null) {
            return false;
        }

        return ! static::query()
            ->where('cycle_id', $this->cycle_id)
            ->where('statut', 'actif')
            ->where('ordre', $this->ordre + 1)
            ->exists();
    }

    /**
     * Un élève admis de cette classe sort-il diplômé ?
     *
     * La fin déclarée sur le cycle fait foi :
     *  - « diplome »    → toujours (fin de parcours explicite) ;
     *  - « certificat » → jamais (certification intermédiaire, l'élève poursuit) ;
     *  - « aucune »     → repli : diplôme seulement si plus aucune classe d'accueil.
     */
    public function donneDiplome(): bool
    {
        if ($this->cycle?->delivreDiplome() === true) {
            return true;
        }

        if ($this->cycle?->delivreCertificat() === true) {
            return false;
        }

        return $this->estTerminale();
    }

    /**
     * Le passage exige-t-il un choix (section/option) par élève ? Vrai dès que
     * plusieurs classes d'accueil sont possibles.
     */
    public function passageAmbigu(): bool
    {
        return $this->candidats()->count() > 1;
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
