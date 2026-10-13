<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Periode;
use App\Support\BulletinDocument;
use App\Support\ClassementClasse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Espace titulaire — bulletins et synthèse de classe.
 *
 * Le titulaire suit les bulletins des classes où il assure au moins une
 * affectation de cours pour l'année en cours : classement, mention, place,
 * appréciations (conduite / application) et document annuel (norme RDC).
 */
class TitulaireBulletinController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $annee = AnneeScolaire::enCours();
        $classes = $this->mesClasses($user, $annee);

        $periodes = Periode::anneeEnCours()->orderBy('id')->get();

        $periodeId = $request->query('periode_id');
        $classeId = $request->query('classe_id');

        $periode = $periodeId ? $periodes->firstWhere('id', (int) $periodeId) : null;
        $classe = $classeId ? $classes->firstWhere('id', (int) $classeId) : null;

        $rows = collect();
        if ($periode && $classe) {
            $rows = collect(ClassementClasse::pour($classe, $periode))->map(function (array $row) use ($periode) {
                $row['bulletin'] = Bulletin::query()
                    ->where('periode_id', $periode->id)
                    ->where('inscription_id', $row['inscription']->id)
                    ->first();

                return $row;
            });
        }

        return view('titulaire.bulletins', [
            'user' => $user,
            'professor' => $user->professeur,
            'annee' => $annee,
            'classes' => $classes,
            'periodes' => $periodes,
            'periode' => $periode,
            'classe' => $classe,
            'periodeId' => $periodeId,
            'classeId' => $classeId,
            'rows' => $rows,
        ]);
    }

    /** Génère / actualise les bulletins de la classe pour la période choisie. */
    public function generate(Request $request): RedirectResponse
    {
        [$classe, $periode, $erreur] = $this->contexte($request);

        if ($erreur) {
            return back()->withInput()->with('error', $erreur);
        }

        $rows = ClassementClasse::pour($classe, $periode);

        if ($rows === []) {
            return back()->with('error', 'Aucun élève inscrit dans cette classe pour l’année en cours.');
        }

        DB::transaction(function () use ($rows, $periode) {
            foreach ($rows as $row) {
                // On préserve les appréciations déjà saisies.
                $existant = Bulletin::query()
                    ->where('inscription_id', $row['inscription']->id)
                    ->where('periode_id', $periode->id)
                    ->first();

                Bulletin::updateOrCreate(
                    ['inscription_id' => $row['inscription']->id, 'periode_id' => $periode->id],
                    [
                        'total_points' => $row['total'],
                        'max_possible' => $row['max'],
                        'pourcentage' => $row['pourcentage'],
                        'place_classe' => $row['place'],
                        'mention' => $row['mention'],
                        'conduite' => $existant?->conduite,
                        'application' => $existant?->application,
                        'statut' => 'actif',
                    ]
                );
            }
        });

        return redirect()
            ->route('titulaire.bulletins.index', ['periode_id' => $periode->id, 'classe_id' => $classe->id])
            ->with('success', 'Les bulletins de '.$classe->nom.' ont été générés.');
    }

    /** Enregistre les appréciations (conduite / application) de la classe. */
    public function appreciations(Request $request): RedirectResponse
    {
        $request->validate([
            'periode_id' => ['required', 'integer'],
            'classe_id' => ['required', 'integer'],
            'conduite' => ['nullable', 'array'],
            'conduite.*' => ['nullable', 'string', 'max:50'],
            'application' => ['nullable', 'array'],
            'application.*' => ['nullable', 'string', 'max:50'],
        ]);

        [$classe, $periode, $erreur] = $this->contexte($request);

        if ($erreur) {
            return back()->withInput()->with('error', $erreur);
        }

        $conduites = (array) $request->input('conduite', []);
        $applications = (array) $request->input('application', []);

        $rows = ClassementClasse::pour($classe, $periode);

        DB::transaction(function () use ($rows, $periode, $conduites, $applications) {
            foreach ($rows as $row) {
                $inscriptionId = $row['inscription']->id;

                Bulletin::updateOrCreate(
                    ['inscription_id' => $inscriptionId, 'periode_id' => $periode->id],
                    [
                        'total_points' => $row['total'],
                        'max_possible' => $row['max'],
                        'pourcentage' => $row['pourcentage'],
                        'place_classe' => $row['place'],
                        'mention' => $row['mention'],
                        'conduite' => $conduites[$inscriptionId] ?? null,
                        'application' => $applications[$inscriptionId] ?? null,
                        'statut' => 'actif',
                    ]
                );
            }
        });

        return redirect()
            ->route('titulaire.bulletins.index', ['periode_id' => $periode->id, 'classe_id' => $classe->id])
            ->with('success', 'Les appréciations ont été enregistrées.');
    }

    /** Bulletin annuel (norme RDC) d'un élève d'une classe du titulaire. */
    public function document(Inscription $inscription): View
    {
        abort_unless(
            $this->maClasse($inscription->classe_id) !== null,
            403,
            'Cet élève n’appartient à aucune de vos classes.'
        );

        return view('admin.bulletins.document', BulletinDocument::donnees($inscription));
    }

    /**
     * Valide le contexte (classe du titulaire + période de l'année en cours).
     *
     * @return array{0: ?Classe, 1: ?Periode, 2: ?string}
     */
    private function contexte(Request $request): array
    {
        $classeId = (int) $request->input('classe_id');
        $periodeId = (int) $request->input('periode_id');

        $classe = $this->maClasse($classeId);

        if (! $classe) {
            return [null, null, 'Cette classe ne fait pas partie de vos classes.'];
        }

        $periode = Periode::anneeEnCours()->find($periodeId);

        if (! $periode) {
            return [null, null, 'Période introuvable pour l’année en cours.'];
        }

        return [$classe, $periode, null];
    }

    private function maClasse(int $classeId): ?Classe
    {
        return $this->mesClasses(request()->user(), AnneeScolaire::enCours())->firstWhere('id', $classeId);
    }

    /**
     * Classes du titulaire : celles où il assure au moins une affectation de cours
     * pour l'année en cours (même règle que le tableau de bord titulaire).
     *
     * @return Collection<int, Classe>
     */
    private function mesClasses($user, ?AnneeScolaire $annee): Collection
    {
        $professeur = $user?->professeur;

        if (! $professeur || ! $annee) {
            return collect();
        }

        $classeIds = AffectationCours::query()
            ->anneeEnCours()
            ->where('professeur_id', $professeur->id)
            ->pluck('classe_id')
            ->unique()
            ->values();

        return Classe::query()
            ->whereIn('id', $classeIds)
            ->with(['cycle', 'salle'])
            ->orderBy('nom')
            ->get();
    }
}
