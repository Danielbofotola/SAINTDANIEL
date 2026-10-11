<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Support\EspaceProfesseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Espace enseignant : appel (présences) des élèves par cours et par séance.
 */
class ProfesseurPresenceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = EspaceProfesseur::annee();
        $affectations = EspaceProfesseur::affectations($professor?->id, $schoolYear?->id);

        // « (int) ( … ?? 0 ) » : les parenthèses sont indispensables, le transtypage
        // étant prioritaire sur ?? (sinon lecture de propriété sur null si la
        // collection est vide).
        $affectationId = (int) ($affectations->first()->id ?? 0);
        $affectation = $affectations->firstWhere('id', $affectationId);

        $date = $request->query('date') ?: today()->toDateString();

        $eleves = collect();
        $presences = collect();
        $historique = collect();

        if ($affectation) {
            $eleves = EspaceProfesseur::eleveParClasse(
                EspaceProfesseur::eleves($affectations, $schoolYear?->id),
                (int) $affectation->classe_id
            );

            $presences = DB::table('presences')
                ->where('affectation_id', $affectation->id)
                ->where('date_seance', $date)
                ->get()
                ->keyBy('eleve_id');

            $historique = DB::table('presences')
                ->where('affectation_id', $affectation->id)
                ->selectRaw('date_seance, COUNT(*) as total,
                    SUM(CASE WHEN statut_presence = ? THEN 1 ELSE 0 END) as absents,
                    SUM(CASE WHEN statut_presence = ? THEN 1 ELSE 0 END) as retards', ['absent', 'retard'])
                ->groupBy('date_seance')
                ->orderByDesc('date_seance')
                ->limit(8)
                ->get();
        }

        $compte = fn (string $statut) => $presences->where('statut_presence', $statut)->count();

        return view('professeur.presences', [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'affectations' => $affectations,
            'affectation' => $affectation,
            'date' => $date,
            'eleves' => $eleves,
            'presences' => $presences,
            'historique' => $historique,
            'statuts' => Presence::STATUTS,
            'bilan' => [
                'presents' => $compte('present'),
                'absents' => $compte('absent'),
                'retards' => $compte('retard'),
                'excuses' => $compte('excuse'),
                'appele' => $presences->count(),
                'total' => $eleves->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $professeurId = EspaceProfesseur::professeurId($request->user());
        $schoolYear = EspaceProfesseur::annee();

        if (! $schoolYear) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’enregistrer l’appel.');
        }

        $affectations = EspaceProfesseur::affectations($professeurId, $schoolYear->id);

        $data = $request->validate([
            'affectation_id' => ['required', Rule::in($affectations->pluck('id')->all())],
            'date_seance' => ['required', 'date'],
            'statuts' => ['required', 'array', 'min:1'],
            'statuts.*' => ['required', Rule::in(array_keys(Presence::STATUTS))],
            'motifs' => ['nullable', 'array'],
            'motifs.*' => ['nullable', 'string', 'max:200'],
        ], [
            'affectation_id.in' => 'Ce cours ne vous est pas affecté.',
            'statuts.required' => 'Sélectionnez au moins un élève pour enregistrer l’appel.',
            'statuts.*.in' => 'Statut de présence inconnu.',
        ]);

        $enregistrees = 0;

        DB::transaction(function () use ($data, $request, &$enregistrees) {
            foreach ($data['statuts'] as $eleveId => $statut) {
                Presence::updateOrCreate(
                    [
                        'affectation_id' => $data['affectation_id'],
                        'eleve_id' => (int) $eleveId,
                        'date_seance' => $data['date_seance'],
                    ],
                    [
                        'statut_presence' => $statut,
                        'motif' => $data['motifs'][$eleveId] ?? null,
                        'enregistre_par' => $request->user()->id,
                    ]
                );

                $enregistrees++;
            }
        });

        return redirect()
            ->route('professeur.presences', ['affectation' => $data['affectation_id'], 'date' => $data['date_seance']])
            ->with('success', 'Appel enregistré : '.$enregistrees.' élève(s) pour le '.date('d/m/Y', strtotime($data['date_seance'])).'.');
    }
}
