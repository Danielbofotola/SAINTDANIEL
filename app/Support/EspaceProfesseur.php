<?php

namespace App\Support;

use App\Models\AnneeScolaire;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Contexte partagé de l'espace enseignant : année en cours, affectations de
 * cours, emploi du temps, élèves des classes et évaluations du professeur.
 */
class EspaceProfesseur
{
    public static function professeurId(User $user): ?int
    {
        return $user->professeur?->id;
    }

    public static function annee(): ?AnneeScolaire
    {
        return AnneeScolaire::query()->where('is_encours', true)->where('statut', 'actif')->first();
    }

    /**
     * Cours affectés au professeur pour l'année en cours.
     */
    public static function affectations(?int $professeurId, ?int $anneeId): Collection
    {
        if (! $professeurId || ! $anneeId) {
            return collect();
        }

        return DB::table('affectations_cours')
            ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
            ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
            ->leftJoin('cycles', 'cycles.id', '=', 'classes.cycle_id')
            ->leftJoin('sections', 'sections.id', '=', 'classes.section_id')
            ->leftJoin('options', 'options.id', '=', 'classes.option_id')
            ->where('affectations_cours.professeur_id', $professeurId)
            ->where('affectations_cours.annee_scolaire_id', $anneeId)
            ->where('affectations_cours.statut', 'actif')
            ->orderBy('classes.nom')
            ->orderBy('cours.intitule')
            ->select([
                'affectations_cours.id',
                'affectations_cours.classe_id',
                'cours.code as cours_code',
                'cours.intitule as cours_nom',
                'classes.nom as classe_nom',
                'cycles.nom as cycle_nom',
                'sections.nom as section_nom',
                'options.nom as option_nom',
            ])
            ->get();
    }

    /**
     * Emploi du temps hebdomadaire des affectations données.
     */
    public static function emplois(Collection $affectations): Collection
    {
        if ($affectations->isEmpty()) {
            return collect();
        }

        return DB::table('emplois_du_temps')
            ->join('affectations_cours', 'affectations_cours.id', '=', 'emplois_du_temps.affectation_id')
            ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
            ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
            ->leftJoin('salles', 'salles.id', '=', 'emplois_du_temps.salle_id')
            ->whereIn('emplois_du_temps.affectation_id', $affectations->pluck('id'))
            ->where('emplois_du_temps.statut', 'actif')
            ->orderByRaw("CASE emplois_du_temps.jour WHEN 'lundi' THEN 1 WHEN 'mardi' THEN 2 WHEN 'mercredi' THEN 3 WHEN 'jeudi' THEN 4 WHEN 'vendredi' THEN 5 WHEN 'samedi' THEN 6 ELSE 7 END")
            ->orderBy('emplois_du_temps.heure_debut')
            ->select([
                'emplois_du_temps.id',
                'emplois_du_temps.jour',
                'emplois_du_temps.heure_debut',
                'emplois_du_temps.heure_fin',
                'emplois_du_temps.affectation_id',
                'cours.intitule as cours_nom',
                'classes.nom as classe_nom',
                'salles.nom as salle_nom',
            ])
            ->get();
    }

    /**
     * Élèves inscrits dans les classes du professeur.
     */
    public static function eleves(Collection $affectations, ?int $anneeId): Collection
    {
        $classeIds = $affectations->pluck('classe_id')->unique()->values();

        if ($classeIds->isEmpty() || ! $anneeId) {
            return collect();
        }

        return DB::table('inscriptions')
            ->join('eleves', 'eleves.id', '=', 'inscriptions.eleve_id')
            ->join('classes', 'classes.id', '=', 'inscriptions.classe_id')
            ->where('inscriptions.annee_scolaire_id', $anneeId)
            ->where('inscriptions.statut', 'actif')
            ->whereIn('inscriptions.classe_id', $classeIds)
            ->orderBy('classes.nom')
            ->orderBy('eleves.nom')
            ->orderBy('eleves.prenom')
            ->select([
                'eleves.id',
                'eleves.matricule',
                'eleves.nom',
                'eleves.postnom',
                'eleves.prenom',
                'eleves.sexe',
                'eleves.photo',
                'inscriptions.classe_id',
                'classes.nom as classe_nom',
            ])
            ->get();
    }

    /**
     * Évaluations des affectations données, avec le nombre de notes saisies.
     */
    public static function evaluations(Collection $affectations): Collection
    {
        if ($affectations->isEmpty()) {
            return collect();
        }

        return DB::table('evaluations')
            ->join('affectations_cours', 'affectations_cours.id', '=', 'evaluations.affectation_id')
            ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
            ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
            ->leftJoin('periodes', 'periodes.id', '=', 'evaluations.periode_id')
            ->whereIn('evaluations.affectation_id', $affectations->pluck('id'))
            ->where('evaluations.statut', 'actif')
            ->orderByDesc('evaluations.date_evaluation')
            ->select([
                'evaluations.id',
                'evaluations.titre',
                'evaluations.type_evaluation',
                'evaluations.max_note',
                'evaluations.date_evaluation',
                'evaluations.affectation_id',
                'cours.intitule as cours_nom',
                'classes.nom as classe_nom',
                'classes.id as classe_id',
                'periodes.nom as periode_nom',
                DB::raw('(SELECT COUNT(*) FROM notes WHERE notes.evaluation_id = evaluations.id AND notes.statut = ?) as notes_count'),
            ])
            ->addBinding('actif', 'select')
            ->get();
    }

    public static function eleveParClasse(Collection $eleves, int $classeId): Collection
    {
        return $eleves->where('classe_id', $classeId)->values();
    }
}
