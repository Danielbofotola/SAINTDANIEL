<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Deliberation;
use App\Models\Inscription;
use App\Models\ParametreSysteme;
use App\Support\Jury;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\DecisionAnnuelleNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Délibération de fin d'année : le jury applique les critères de
 * l'établissement à la moyenne du bulletin, décide pour chaque élève, puis
 * matérialise le passage en créant les inscriptions de l'année suivante.
 *
 * Aucune moyenne n'est recalculée ici : tout vient de `BulletinRdc`.
 */
class DeliberationController extends Controller
{
    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();
        $classes = Classe::query()
            ->where('statut', 'actif')
            ->orderByRaw('ordre is null')
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        $classe = $request->query('classe')
            ? $classes->firstWhere('id', (int) $request->query('classe'))
            : $classes->first();

        $jury = Jury::regles();
        $lignes = collect();

        if ($annee && $classe) {
            $inscriptions = Inscription::query()
                ->with('eleve')
                ->where('annee_scolaire_id', $annee->id)
                ->where('classe_id', $classe->id)
                ->where('statut', Inscription::STATUT_ACTIF)
                ->get();

            $enregistrees = Deliberation::query()
                ->where('annee_scolaire_id', $annee->id)
                ->whereIn('inscription_id', $inscriptions->pluck('id'))
                ->get()
                ->keyBy('inscription_id');

            foreach ($inscriptions as $inscription) {
                $analyse = $jury->analyser($inscription);
                $enregistree = $enregistrees->get($inscription->id);

                $lignes->push([
                    'inscription' => $inscription,
                    'eleve' => $inscription->eleve,
                    'analyse' => $analyse,
                    'enregistree' => $enregistree,
                    'decision' => $enregistree?->decision ?? $analyse['decision_proposee'],
                ]);
            }
        }

        return view('admin.deliberations.index', [
            'parametres' => ParametreSysteme::current(),
            'annee' => $annee,
            'anneeSuivante' => $annee ? AnneeScolaire::suivante($annee) : null,
            'classes' => $classes,
            'classe' => $classe,
            'lignes' => $lignes,
            'synthese' => $this->synthese($lignes),
            'decisions' => Deliberation::DECISIONS,
            'annees' => AnneeScolaire::query()->orderByDesc('date_debut')->get(),
            'classeSuivante' => $classe?->suivante(),
            'regles' => $jury->critere(),
        ]);
    }

    /**
     * Synthèse de l'effectif délibéré : compte par décision et répartition
     * garçons / filles (globale et par décision), pour les cartes d'en-tête.
     *
     * @param  Collection<int, array{eleve: ?\App\Models\Eleve, decision: string}>  $lignes
     * @return array{total: int, garcons: int, filles: int, decisions: array<string, array{total: int, g: int, f: int}>}
     */
    private function synthese(Collection $lignes): array
    {
        $decisions = [];
        $garcons = 0;
        $filles = 0;

        foreach ($lignes as $ligne) {
            $decision = $ligne['decision'];
            $decisions[$decision] ??= ['total' => 0, 'g' => 0, 'f' => 0];
            $decisions[$decision]['total']++;

            if ($ligne['eleve']?->sexe === 'M') {
                $decisions[$decision]['g']++;
                $garcons++;
            } elseif ($ligne['eleve']?->sexe === 'F') {
                $decisions[$decision]['f']++;
                $filles++;
            }
        }

        foreach (array_keys(Deliberation::DECISIONS) as $cle) {
            $decisions[$cle] ??= ['total' => 0, 'g' => 0, 'f' => 0];
        }

        return [
            'total' => $lignes->count(),
            'garcons' => $garcons,
            'filles' => $filles,
            'decisions' => $decisions,
        ];
    }

    /** Enregistre les critères de délibération (seuils et plafond). */
    public function criteres(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'deliberation_seuil_admission' => ['required', 'numeric', 'min:0', 'max:100'],
            'deliberation_seuil_repechage' => ['required', 'numeric', 'min:0', 'max:100'],
            'deliberation_max_cours_echoues' => ['nullable', 'integer', 'min:0', 'max:50'],
            'deliberation_note_min_cours' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'deliberation_seuil_admission.required' => 'Indiquez le seuil d’admission.',
            'deliberation_seuil_repechage.required' => 'Indiquez le seuil de repêchage.',
        ]);

        if ((float) $data['deliberation_seuil_repechage'] > (float) $data['deliberation_seuil_admission']) {
            return back()->withInput()->with('error', 'Le seuil de repêchage ne peut pas dépasser le seuil d’admission.');
        }

        $parametres = ParametreSysteme::query()->first()
            ?? ParametreSysteme::create(ParametreSysteme::defaults()->getAttributes());

        $parametres->update([
            'deliberation_active' => $request->boolean('deliberation_active'),
            'deliberation_seuil_admission' => $data['deliberation_seuil_admission'],
            'deliberation_seuil_repechage' => $data['deliberation_seuil_repechage'],
            'deliberation_max_cours_echoues' => $data['deliberation_max_cours_echoues'] ?? null,
            'deliberation_note_min_cours' => $data['deliberation_note_min_cours'] ?? null,
        ]);

        return back()->with('success', 'Critères de délibération enregistrés.');
    }

    /** Enregistre en masse les décisions d'un effectif (proposées ou forcées). */
    public function enregistrer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'decisions' => ['required', 'array'],
            'decisions.*' => ['required', 'in:admis,ajourne,redouble'],
        ], [
            'decisions.required' => 'Aucune décision à enregistrer.',
        ]);

        $jury = Jury::regles();
        $critere = $jury->critere();
        $total = 0;
        $notifies = 0;

        DB::transaction(function () use ($data, $request, $jury, $critere, &$total, &$notifies): void {
            $inscriptions = Inscription::query()
                ->with(['classe', 'eleve.parents'])
                ->whereIn('id', array_keys($data['decisions']))
                ->where('annee_scolaire_id', $data['annee_scolaire_id'])
                ->get();

            foreach ($inscriptions as $inscription) {
                $analyse = $jury->analyser($inscription);
                $choix = $data['decisions'][$inscription->id];

                $deliberation = Deliberation::updateOrCreate(
                    ['inscription_id' => $inscription->id, 'annee_scolaire_id' => $data['annee_scolaire_id']],
                    [
                        'pourcentage_general' => $analyse['pourcentage'],
                        'cours_echoues' => $analyse['cours_echoues'],
                        'decision' => $choix,
                        'critere' => json_encode($critere),
                        'manuelle' => $choix !== $analyse['decision_proposee'],
                        'classe_suivante_id' => $choix === 'admis' ? $inscription->classe?->suivante()?->id : null,
                        'decide_par' => $request->user()->id,
                        'decide_le' => now(),
                    ]
                );
                $total++;

                // Le verdict de fin d'année est notifié à chaque compte parent
                // rattaché à l'élève (cloche + « Mes notifications » du portail).
                $parents = $inscription->eleve?->parents ?? collect();

                if ($parents->isNotEmpty()) {
                    Notification::send($parents, new DecisionAnnuelleNotification($deliberation));
                    $notifies += $parents->count();
                }
            }
        });

        return back()->with('success', $total.' décision(s) enregistrée(s) — '.$notifies.' notification(s) envoyée(s) aux comptes parents.');
    }

    /**
     * Applique en masse le passage : les admis montent dans la classe d'ordre
     * supérieur, les ajournés et redoublants restent dans la même classe, en
     * créant les inscriptions de l'année cible.
     */
    public function appliquer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'annee_cible_id' => ['required', 'exists:annees_scolaires,id', 'different:annee_scolaire_id'],
        ], [
            'annee_cible_id.different' => 'L’année cible doit être différente de l’année délibérée.',
        ]);

        $annee = AnneeScolaire::findOrFail($data['annee_scolaire_id']);
        $cible = AnneeScolaire::findOrFail($data['annee_cible_id']);

        // Le passage va toujours vers l'année suivante : une cible antérieure
        // (ou égale) à l'année délibérée n'a pas de sens.
        if ($cible->date_debut <= $annee->date_debut) {
            return back()->with('error', 'L’année cible doit être postérieure à l’année délibérée.');
        }

        $promus = 0;
        $maintenus = 0;
        $ignores = 0;

        DB::transaction(function () use ($data, &$promus, &$maintenus, &$ignores): void {
            $decisions = Deliberation::query()
                ->with('inscription.classe')
                ->where('annee_scolaire_id', $data['annee_scolaire_id'])
                ->get();

            foreach ($decisions as $decision) {
                $inscription = $decision->inscription;
                if (! $inscription) {
                    $ignores++;
                    continue;
                }

                // Admis → classe d'ordre supérieur ; ajourné / redoublant → même classe.
                $classeCible = $decision->decision === 'admis'
                    ? $inscription->classe?->suivante()
                    : $inscription->classe;

                if (! $classeCible) {
                    $ignores++;   // plus de classe supérieure, ou classe d'origine absente
                    continue;
                }

                $dejaInscrit = Inscription::query()
                    ->where('eleve_id', $inscription->eleve_id)
                    ->where('annee_scolaire_id', $data['annee_cible_id'])
                    ->exists();

                if ($dejaInscrit) {
                    $ignores++;
                    continue;
                }

                Inscription::create([
                    'eleve_id' => $inscription->eleve_id,
                    'classe_id' => $classeCible->id,
                    'annee_scolaire_id' => $data['annee_cible_id'],
                    'date_inscription' => now()->toDateString(),
                    'statut' => Inscription::STATUT_EN_ATTENTE,
                ]);

                $decision->update(['classe_suivante_id' => $classeCible->id]);
                $decision->decision === 'admis' ? $promus++ : $maintenus++;
            }
        });

        return back()->with('success', $promus.' élève(s) promu(s), '.$maintenus.' maintenu(s), '.$ignores.' ignoré(s).');
    }
}
