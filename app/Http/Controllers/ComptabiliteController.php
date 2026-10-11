<?php

namespace App\Http\Controllers;

use App\Models\CompteComptable;
use App\Models\Depense;
use App\Models\Ecriture;
use App\Models\LigneEcriture;
use App\Models\Paie;
use App\Models\Paiement;
use App\Support\Comptabilite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Comptabilité OHADA : plan comptable SYSCOHADA, écritures en partie double,
 * balance générale et synchronisation des opérations du système.
 */
class ComptabiliteController extends Controller
{
    private const CLASSES = [
        1 => 'Ressources durables',
        2 => 'Actif immobilisé',
        3 => 'Stocks',
        4 => 'Tiers',
        5 => 'Trésorerie',
        6 => 'Charges',
        7 => 'Produits',
        8 => 'Autres charges et produits',
        9 => 'Comptabilité analytique',
    ];

    public function index(Request $request): View
    {
        return view('admin.comptabilite.index', [
            'user' => $request->user(),
            'totaux' => [
                'debit' => round((float) LigneEcriture::sum('debit'), 2),
                'credit' => round((float) LigneEcriture::sum('credit'), 2),
                'ecritures' => Ecriture::count(),
                'comptes' => CompteComptable::count(),
            ],
            'aComptabiliser' => [
                'paiements' => Paiement::query()->where('statut', 'actif')
                    ->whereNotIn('id', $this->sourcesDejaComptabilisees('paiement'))->count(),
                'depenses' => Depense::query()->where('statut', 'actif')
                    ->whereNotIn('id', $this->sourcesDejaComptabilisees('depense'))->count(),
                'paies' => Paie::query()->where('statut', 'actif')
                    ->whereNotIn('id', $this->sourcesDejaComptabilisees('paie'))->count(),
            ],
            'ecritures' => Ecriture::with('lignes.compte')->latest('date_ecriture')->latest('id')->limit(30)->get(),
            'comptes' => CompteComptable::orderBy('numero')->get(),
            'classes' => self::CLASSES,
            'typesCompte' => ['actif' => 'Actif', 'passif' => 'Passif', 'charge' => 'Charge', 'produit' => 'Produit'],
        ]);
    }

    public function balance(Request $request): View
    {
        $balance = Comptabilite::balance();

        return view('admin.comptabilite.balance', [
            'user' => $request->user(),
            'balance' => $balance,
            'totalDebit' => round((float) $balance->sum('debit'), 2),
            'totalCredit' => round((float) $balance->sum('credit'), 2),
            'parClasse' => $balance->groupBy('classe')->map(fn ($lignes) => [
                'debit' => round((float) $lignes->sum('debit'), 2),
                'credit' => round((float) $lignes->sum('credit'), 2),
                'solde' => round((float) $lignes->sum('solde'), 2),
            ]),
            'classes' => self::CLASSES,
        ]);
    }

    public function plan(Request $request): View
    {
        return view('admin.comptabilite.plan', [
            'user' => $request->user(),
            'comptes' => CompteComptable::withCount('lignes')->orderBy('numero')->get(),
            'classes' => self::CLASSES,
            'typesCompte' => ['actif' => 'Actif', 'passif' => 'Passif', 'charge' => 'Charge', 'produit' => 'Produit'],
        ]);
    }

    public function storeCompte(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'numero' => ['required', 'string', 'max:10', 'unique:comptes_comptables,numero'],
            'libelle' => ['required', 'string', 'max:150'],
            'classe' => ['required', 'integer', 'min:1', 'max:9'],
            'type' => ['required', Rule::in(['actif', 'passif', 'charge', 'produit'])],
        ], [
            'numero.unique' => 'Ce numéro de compte existe déjà dans le plan comptable.',
            'libelle.required' => 'Le libellé du compte est obligatoire.',
        ]);

        CompteComptable::create($data + ['statut' => 'actif']);

        return back()->with('success', 'Le compte '.$data['numero'].' a été ajouté au plan comptable.');
    }

    public function destroyCompte(CompteComptable $compte): RedirectResponse
    {
        if ($compte->lignes()->exists()) {
            return back()->with('error', 'Ce compte est mouvementé par des écritures : il ne peut pas être supprimé.');
        }

        $compte->delete();

        return back()->with('success', 'Le compte a été supprimé du plan comptable.');
    }

    public function storeEcriture(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_ecriture' => ['required', 'date'],
            'journal' => ['required', 'string', 'max:10'],
            'libelle' => ['required', 'string', 'max:200'],
            'reference' => ['nullable', 'string', 'max:100'],
            'lignes' => ['required', 'array', 'min:2'],
            'lignes.*.compte_id' => ['required', Rule::exists('comptes_comptables', 'id')],
            'lignes.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.credit' => ['nullable', 'numeric', 'min:0'],
        ], [
            'lignes.required' => 'Saisissez au moins deux lignes (un débit et un crédit).',
            'lignes.min' => 'Une écriture doit comporter au moins deux lignes.',
            'lignes.*.compte_id.required' => 'Chaque ligne doit préciser un compte.',
            'date_ecriture.required' => 'La date de l’écriture est obligatoire.',
        ]);

        $lignes = [];
        foreach ($data['lignes'] as $ligne) {
            $compte = CompteComptable::find($ligne['compte_id']);

            if (! $compte) {
                continue;
            }

            $lignes[] = [
                'numero' => $compte->numero,
                'debit' => $ligne['debit'] ?? 0,
                'credit' => $ligne['credit'] ?? 0,
            ];
        }

        try {
            $ecriture = Comptabilite::ecriture(
                $data['date_ecriture'],
                $data['journal'],
                $data['libelle'],
                $lignes,
                $data['reference'] ?? null
            );
        } catch (RuntimeException) {
            return back()->withInput()->with('error', 'Écriture refusée : le total des débits doit être égal au total des crédits (et au moins deux lignes mouvementées).');
        }

        return back()->with('success', 'Écriture n° '.$ecriture->id.' enregistrée et équilibrée.');
    }

    public function destroyEcriture(Ecriture $ecriture): RedirectResponse
    {
        $ecriture->delete();

        return back()->with('success', 'L’écriture a été supprimée (ses lignes aussi).');
    }

    public function synchroniser(): RedirectResponse
    {
        $compteurs = Comptabilite::synchroniser();

        $total = array_sum($compteurs);

        if ($total === 0) {
            return back()->with('success', 'Tout est déjà comptabilisé : aucune nouvelle opération à synchroniser.');
        }

        return back()->with('success', 'Synchronisation terminée : '.$compteurs['paiements'].' paiement(s), '
            .$compteurs['depenses'].' dépense(s), '.$compteurs['paies'].' salaire(s) comptabilisés.');
    }

    /**
     * Identifiants des opérations déjà présentes en comptabilité pour un type.
     */
    private function sourcesDejaComptabilisees(string $type)
    {
        return Ecriture::query()
            ->where('source_type', $type)
            ->whereNotNull('source_id')
            ->pluck('source_id');
    }
}
