<?php

namespace App\Support;

use App\Models\Inscription;

/**
 * Identifiant unique d'un bulletin annuel : un code lisible (affiché sous le QR)
 * et la charge encodée dans le QR, dérivés de l'inscription et signés, pour
 * qu'un bulletin ne puisse pas être confondu avec un autre.
 */
class BulletinUnique
{
    /**
     * @return array{code: string, charge: string, url: string}
     */
    public static function donnees(Inscription $inscription): array
    {
        $code = self::code($inscription);
        $annee = (string) ($inscription->anneeScolaire?->libelle ?? '');
        $matricule = (string) ($inscription->eleve?->matricule ?? '');

        // Charge du QR : chaîne stable et vérifiable pour cette inscription.
        $charge = implode('|', ['BULLETIN', $code, $matricule, $annee]);

        return [
            'code' => $code,
            'charge' => $charge,
            'url' => url('/verif/bulletin/'.$code),
        ];
    }

    /** Code public unique : BUL-<année>-<n° inscription>-<contrôle>. */
    public static function code(Inscription $inscription): string
    {
        $annee = preg_replace('/\D/', '', (string) ($inscription->anneeScolaire?->libelle ?? ''));

        $controle = strtoupper(substr(hash_hmac('sha256', implode('|', [
            $inscription->id,
            $inscription->eleve_id,
            $inscription->annee_scolaire_id,
            $inscription->classe_id,
        ]), (string) config('app.key')), 0, 8));

        return sprintf('BUL-%s-%06d-%s', $annee !== '' ? $annee : '00000000', (int) $inscription->id, $controle);
    }
}
