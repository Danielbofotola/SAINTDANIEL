<?php

namespace App\Support;

use App\Models\Paie as ModelePaie;
use App\Models\Salaire;
use RuntimeException;

/**
 * Paiement d'un salaire : calcule le net (base + primes − retenues) et génère
 * un numéro de paie unique. Une période (un mois) n'est payée qu'une fois par
 * bénéficiaire.
 */
class Paie
{
    /**
     * @throws RuntimeException lorsque la période est déjà payée pour ce salaire.
     */
    public static function enregistrer(Salaire $salaire, array $data): ModelePaie
    {
        $periode = (string) $data['periode'];

        if (ModelePaie::query()->where('salaire_id', $salaire->id)->where('periode', $periode)->exists()) {
            throw new RuntimeException('periode_deja_payee');
        }

        $base = (float) $salaire->salaire_base;
        $primes = (float) ($data['primes'] ?? 0);
        $retenues = (float) ($data['retenues'] ?? 0);
        $net = max($base + $primes - $retenues, 0);

        $prefix = 'PAIE-'.$periode.'-';
        $numero = ModelePaie::where('num_paie', 'like', $prefix.'%')->count() + 1;
        do {
            $numPaie = $prefix.str_pad((string) $numero++, 4, '0', STR_PAD_LEFT);
        } while (ModelePaie::where('num_paie', $numPaie)->exists());

        return ModelePaie::create([
            'num_paie' => $numPaie,
            'salaire_id' => $salaire->id,
            'periode' => $periode,
            'montant_base' => $base,
            'primes' => $primes,
            'retenues' => $retenues,
            'montant_net' => $net,
            'date_paiement' => $data['date_paiement'] ?? now()->toDateString(),
            'mode_paiement' => $data['mode_paiement'],
            'reference' => $data['reference'] ?? null,
            'observation' => $data['observation'] ?? null,
            'statut' => 'actif',
        ]);
    }
}
