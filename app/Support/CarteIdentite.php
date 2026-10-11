<?php

namespace App\Support;

use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\Professeurs;

/**
 * Carte d'identité (badge) d'un élève ou d'un professeur : un code unique signé
 * et la charge encodée dans le QR de la carte. Sert aussi bien au badge
 * professionnel qu'à la carte de paiement présentée à la caisse.
 */
class CarteIdentite
{
    /**
     * @return array{code: string, charge: string, matricule: string}
     */
    public static function eleve(Eleve $eleve, ?Inscription $inscription = null): array
    {
        $matricule = (string) ($eleve->matricule ?? '');
        $classe = $inscription?->classe?->nom;
        $annee = (string) ($inscription?->anneeScolaire?->libelle ?? '');

        return [
            'code' => self::code('ELV', $matricule, (int) $eleve->id),
            'charge' => implode('|', array_filter(['ELEVE', $matricule, $classe, $annee])),
            'matricule' => $matricule,
        ];
    }

    /**
     * @return array{code: string, charge: string, matricule: string}
     */
    public static function professeur(Professeurs $professeur): array
    {
        $matricule = (string) ($professeur->matricule ?? '');

        return [
            'code' => self::code('PRF', $matricule, (int) $professeur->id),
            'charge' => implode('|', array_filter(['PROFESSEUR', $matricule, $professeur->specialite])),
            'matricule' => $matricule,
        ];
    }

    /** Code public unique : <PRÉFIXE>-<matricule ou n° id>-<contrôle 8>. */
    private static function code(string $prefixe, string $matricule, int $id): string
    {
        $reference = $matricule !== '' ? $matricule : str_pad((string) $id, 6, '0', STR_PAD_LEFT);

        $controle = strtoupper(substr(
            hash_hmac('sha256', $prefixe.'|'.$id.'|'.$reference, (string) config('app.key')),
            0,
            8
        ));

        return $prefixe.'-'.$reference.'-'.$controle;
    }
}
