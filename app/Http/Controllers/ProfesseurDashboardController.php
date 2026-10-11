<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfesseurDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = $professor
            ? AnneeScolaire::query()->where('is_encours', true)->where('statut', 'actif')->first()
            : null;

        $assignments = collect();
        $weeklySchedule = collect();
        $upcomingEvaluations = collect();
        $studentCount = 0;

        if ($professor && $schoolYear) {
            $assignments = DB::table('affectations_cours')
                ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
                ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
                ->leftJoin('cycles', 'cycles.id', '=', 'classes.cycle_id')
                ->leftJoin('sections', 'sections.id', '=', 'classes.section_id')
                ->leftJoin('options', 'options.id', '=', 'classes.option_id')
                ->where('affectations_cours.professeur_id', $professor->id)
                ->where('affectations_cours.annee_scolaire_id', $schoolYear->id)
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

            $classIds = $assignments->pluck('classe_id')->unique()->values();

            if ($classIds->isNotEmpty()) {
                $studentCount = DB::table('inscriptions')
                    ->where('annee_scolaire_id', $schoolYear->id)
                    ->where('statut', 'actif')
                    ->whereIn('classe_id', $classIds)
                    ->distinct()
                    ->count('eleve_id');

                $assignmentIds = $assignments->pluck('id');
                $weeklySchedule = DB::table('emplois_du_temps')
                    ->join('affectations_cours', 'affectations_cours.id', '=', 'emplois_du_temps.affectation_id')
                    ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
                    ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
                    ->leftJoin('salles', 'salles.id', '=', 'emplois_du_temps.salle_id')
                    ->whereIn('affectations_cours.id', $assignmentIds)
                    ->where('emplois_du_temps.statut', 'actif')
                    ->orderByRaw("CASE emplois_du_temps.jour WHEN 'lundi' THEN 1 WHEN 'mardi' THEN 2 WHEN 'mercredi' THEN 3 WHEN 'jeudi' THEN 4 WHEN 'vendredi' THEN 5 WHEN 'samedi' THEN 6 ELSE 7 END")
                    ->orderBy('emplois_du_temps.heure_debut')
                    ->select([
                        'emplois_du_temps.jour',
                        'emplois_du_temps.heure_debut',
                        'emplois_du_temps.heure_fin',
                        'cours.intitule as cours_nom',
                        'classes.nom as classe_nom',
                        'salles.nom as salle_nom',
                    ])
                    ->get();

                $upcomingEvaluations = DB::table('evaluations')
                    ->join('affectations_cours', 'affectations_cours.id', '=', 'evaluations.affectation_id')
                    ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
                    ->join('classes', 'classes.id', '=', 'affectations_cours.classe_id')
                    ->whereIn('affectations_cours.id', $assignmentIds)
                    ->where('evaluations.statut', 'actif')
                    ->whereDate('evaluations.date_evaluation', '>=', today())
                    ->orderBy('evaluations.date_evaluation')
                    ->limit(4)
                    ->select([
                        'evaluations.titre',
                        'evaluations.type_evaluation',
                        'evaluations.date_evaluation',
                        'cours.intitule as cours_nom',
                        'classes.nom as classe_nom',
                    ])
                    ->get();
            }
        }

        $now = now();
        $weekdayNames = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $todaySchedule = $weeklySchedule
            ->where('jour', $weekdayNames[$now->dayOfWeekIso])
            ->filter(fn (object $item): bool => $item->heure_debut >= $now->format('H:i:s'))
            ->values();

        $nextClass = $this->findNextClass($weeklySchedule, $now, $weekdayNames);

        return view('professeur.dashboard', [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'assignments' => $assignments,
            'weeklySchedule' => $weeklySchedule,
            'todaySchedule' => $todaySchedule,
            'nextClass' => $nextClass,
            'upcomingEvaluations' => $upcomingEvaluations,
            'studentCount' => $studentCount,
        ]);
    }

    private function findNextClass(Collection $schedule, Carbon $now, array $weekdayNames): ?object
    {
        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $weekdayIndex = (($now->dayOfWeekIso - 1 + $dayOffset) % 7) + 1;
            $weekday = $weekdayNames[$weekdayIndex];
            $nextClass = $schedule->first(fn (object $item): bool =>
                $item->jour === $weekday && ($dayOffset > 0 || $item->heure_debut > $now->format('H:i:s'))
            );

            if ($nextClass) {
                $nextClass->day_offset = $dayOffset;

                return $nextClass;
            }
        }

        return null;
    }
}