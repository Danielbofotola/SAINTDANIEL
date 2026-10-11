<?php

namespace App\Support;

/**
 * Devises et taux de change : le dollar (USD) et le franc congolais (CDF), avec
 * le taux « CDF pour 1 USD » qui sert à convertir un paiement d'une devise vers
 * celle du tarif (la devise de référence des totaux).
 */
class Monnaie
{
    public const USD = 'USD';
    public const CDF = 'CDF';
    public const TAUX_DEFAUT = 2800.0;

    /** Libellés proposés dans les formulaires. */
    public const DEVISES = [
        'USD' => 'Dollar ($)',
        'CDF' => 'Franc congolais (FC)',
    ];

    /** Normalise un code devise : tout ce qui n'est pas CDF devient USD. */
    public static function normaliser(?string $devise): string
    {
        return strtoupper((string) $devise) === self::CDF ? self::CDF : self::USD;
    }

    /** Symbole affiché : « $ » pour l'USD, « FC » pour le franc. */
    public static function symbole(?string $devise): string
    {
        return self::normaliser($devise) === self::CDF ? 'FC' : '$';
    }

    /** Décimales d'affichage : 2 pour l'USD, aucune pour le franc. */
    public static function decimales(?string $devise): int
    {
        return self::normaliser($devise) === self::CDF ? 0 : 2;
    }

    /** Formate un montant dans sa devise : « 50,00 $ » ou « 140 000 FC ». */
    public static function format($montant, ?string $devise = self::USD): string
    {
        return number_format((float) $montant, self::decimales($devise), ',', ' ')
            .' '.self::symbole($devise);
    }

    /**
     * Convertit un montant d'une devise vers une autre au taux « CDF pour 1 USD ».
     * Un taux nul ou négatif laisse le montant inchangé.
     */
    public static function convertir($montant, ?string $de, ?string $vers, float $taux): float
    {
        $de = self::normaliser($de);
        $vers = self::normaliser($vers);
        $montant = (float) $montant;

        if ($de === $vers || $taux <= 0) {
            return round($montant, 2);
        }

        return $de === self::CDF
            ? round($montant / $taux, 2)
            : round($montant * $taux, 2);
    }
}
