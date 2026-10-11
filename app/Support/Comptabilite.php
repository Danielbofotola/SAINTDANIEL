<?php

namespace App\Support;

use App\Models\CompteComptable;
use App\Models\Depense;
use App\Models\Ecriture;
use App\Models\LigneEcriture;
use App\Models\Paie;
use App\Models\Paiement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Comptabilité SYSCOHADA (OHADA) : saisie d'écritures équilibrées, balance
 * générale, et synchronisation automatique avec les opérations du système
 * (paiements d'élèves, dépenses, salaires versés).
 */
class Comptabilite
{
    /**
     * Comptes de charge utilisés selon le libellé de la nature de dépense.
     */
    private const CHARGE_PAR_MOT_CLE = [
        'fournitur' => '605', 'crai' => '605', 'papeteri' => '605', 'eau' => '605', 'electric' => '605',
        'entretien' => '624', 'repar' => '624', 'maintenance' => '624',
        'transport' => '611', 'carburant' => '611',
        'loyer' => '622', 'location' => '622',
        'assur' => '625',
        'publicit' => '627',
        'banque' => '631',
        'honoraire' => '632', 'intermediaire' => '632',
        'impot' => '641', 'taxe' => '641',
        'salaire' => '661', 'personnel' => '661', 'prime' => '668',
    ];

    /**
     * Crée une écriture en partie double. Les lignes vides sont ignorées et
     * l'écriture est refusée si débit ≠ crédit.
     *
     * @param  array<int, array{numero: string, debit?: float|string, credit?: float|string, libelle?: string}>  $lignes
     *
     * @throws RuntimeException si l'écriture est déséquilibrée ou un compte inconnu.
     */
    public static function ecriture(
        string $date,
        string $journal,
        string $libelle,
        array $lignes,
        ?string $reference = null,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): Ecriture {
        $normalisees = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lignes as $ligne) {
            $debit = round((float) ($ligne['debit'] ?? 0), 2);
            $credit = round((float) ($ligne['credit'] ?? 0), 2);

            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            $compte = CompteComptable::where('numero', (string) $ligne['numero'])->first();

            if (! $compte) {
                throw new RuntimeException('compte_introuvable');
            }

            $normalisees[] = [
                'compte_id' => $compte->id,
                'libelle' => $ligne['libelle'] ?? $libelle,
                'debit' => $debit,
                'credit' => $credit,
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (count($normalisees) < 2 || abs($totalDebit - $totalCredit) > 0.005) {
            throw new RuntimeException('ecriture_desequilibree');
        }

        return DB::transaction(function () use ($date, $journal, $libelle, $reference, $sourceType, $sourceId, $normalisees, $totalDebit, $totalCredit) {
            $ecriture = Ecriture::create([
                'date_ecriture' => $date,
                'journal' => $journal,
                'libelle' => $libelle,
                'reference' => $reference,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'statut' => 'validee',
            ]);

            $ecriture->lignes()->createMany($normalisees);

            return $ecriture->load('lignes.compte');
        });
    }

    /**
     * Compte de trésorerie selon le mode de paiement.
     */
    public static function compteTresorerie(string $modePaiement): string
    {
        return in_array($modePaiement, ['banque', 'mobile_money'], true) ? '521' : '571';
    }

    /**
     * Compte de charge correspondant à une nature de dépense (658 par défaut).
     */
    public static function compteChargePour(?string $nature): string
    {
        $nature = mb_strtolower((string) $nature);

        foreach (self::CHARGE_PAR_MOT_CLE as $motCle => $numero) {
            if ($motCle !== '' && str_contains($nature, $motCle)) {
                return $numero;
            }
        }

        return '658';
    }

    /**
     * Synchronise les opérations du système en écritures comptables.
     * Idempotent : une opération déjà comptabilisée n'est jamais dupliquée.
     *
     * @return array{paiements: int, depenses: int, paies: int}
     */
    public static function synchroniser(): array
    {
        $compteurs = ['paiements' => 0, 'depenses' => 0, 'paies' => 0];

        foreach (Paiement::query()->where('statut', 'actif')->with('inscription.eleve')->get() as $paiement) {
            if (self::dejaComptabilise('paiement', $paiement->id)) {
                continue;
            }

            $montant = (float) $paiement->montant_paye;
            if ($montant <= 0) {
                continue;
            }

            $eleve = trim(($paiement->inscription?->eleve?->nom ?? '').' '.($paiement->inscription?->eleve?->prenom ?? ''));

            self::ecriture(
                ($paiement->date_paiement ?? now())->toDateString(),
                'VE',
                'Encaissement reçu '.$paiement->num_recu.($eleve !== '' ? ' — '.$eleve : ''),
                [
                    ['numero' => self::compteTresorerie((string) $paiement->mode_paiement), 'debit' => $montant, 'libelle' => 'Encaissement '.$paiement->num_recu],
                    ['numero' => '706', 'credit' => $montant, 'libelle' => 'Frais scolaires'],
                ],
                $paiement->num_recu,
                'paiement',
                $paiement->id
            );

            $compteurs['paiements']++;
        }

        foreach (Depense::query()->where('statut', 'actif')->with('categorie')->get() as $depense) {
            if (self::dejaComptabilise('depense', $depense->id)) {
                continue;
            }

            $montant = (float) $depense->montant;
            if ($montant <= 0) {
                continue;
            }

            self::ecriture(
                ($depense->date_depense ?? now())->toDateString(),
                'CA',
                'Dépense : '.$depense->motif,
                [
                    ['numero' => self::compteChargePour($depense->categorie?->nom), 'debit' => $montant, 'libelle' => $depense->motif],
                    ['numero' => '571', 'credit' => $montant, 'libelle' => 'Règlement par la caisse'],
                ],
                $depense->piece_justificative,
                'depense',
                $depense->id
            );

            $compteurs['depenses']++;
        }

        foreach (Paie::query()->where('statut', 'actif')->with('salaire.beneficiaire')->get() as $paie) {
            if (self::dejaComptabilise('paie', $paie->id)) {
                continue;
            }

            $montant = (float) $paie->montant_net;
            if ($montant <= 0) {
                continue;
            }

            $nom = $paie->salaire?->nomBeneficiaire();

            self::ecriture(
                ($paie->date_paiement ?? now())->toDateString(),
                'PA',
                'Salaire '.$paie->periode.($nom ? ' — '.$nom : ''),
                [
                    ['numero' => '661', 'debit' => $montant, 'libelle' => 'Rémunération '.$paie->periode],
                    ['numero' => '571', 'credit' => $montant, 'libelle' => 'Paiement du salaire'],
                ],
                $paie->num_paie,
                'paie',
                $paie->id
            );

            $compteurs['paies']++;
        }

        return $compteurs;
    }

    public static function dejaComptabilise(string $type, int $id): bool
    {
        return Ecriture::query()->where('source_type', $type)->where('source_id', $id)->exists();
    }

    /**
     * Balance générale : par compte, total débit, total crédit et solde.
     */
    public static function balance(): Collection
    {
        return LigneEcriture::query()
            ->join('comptes_comptables', 'comptes_comptables.id', '=', 'lignes_ecriture.compte_id')
            ->selectRaw('comptes_comptables.numero as numero, comptes_comptables.libelle as libelle, comptes_comptables.classe as classe, SUM(lignes_ecriture.debit) as debit, SUM(lignes_ecriture.credit) as credit')
            ->groupBy('comptes_comptables.numero', 'comptes_comptables.libelle', 'comptes_comptables.classe')
            ->orderBy('comptes_comptables.numero')
            ->get()
            ->map(fn ($ligne) => (object) [
                'numero' => $ligne->numero,
                'libelle' => $ligne->libelle,
                'classe' => (int) $ligne->classe,
                'debit' => round((float) $ligne->debit, 2),
                'credit' => round((float) $ligne->credit, 2),
                'solde' => round((float) $ligne->debit - (float) $ligne->credit, 2),
            ]);
    }
}
