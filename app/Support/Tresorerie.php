<?php

namespace App\Support;

use App\Models\AnneeScolaire;
use App\Models\Depense;
use App\Models\Paie;
use App\Models\Paiement;

/**
 * État de trésorerie et alertes de charge : les « charges » regroupent les
 * dépenses et les salaires versés. Le système signale une trésorerie négative
 * ou un niveau de charges trop élevé par rapport aux recettes.
 */
class Tresorerie
{
    /**
     * Seuil (en % des recettes) au-delà duquel la charge est signalée.
     */
    public const SEUIL_CHARGES = 80.0;

    public static function etat(?AnneeScolaire $annee = null): array
    {
        $annee ??= AnneeScolaire::enCours();

        $recettes = (float) Paiement::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id)))
            ->sum('montant_paye');

        $depenses = (float) Depense::query()
            ->where('statut', 'actif')
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->sum('montant');

        $requetePaies = Paie::query()->where('statut', 'actif');
        if ($annee?->date_debut && $annee?->date_fin) {
            $requetePaies->whereBetween('periode', [$annee->date_debut->format('Y-m'), $annee->date_fin->format('Y-m')]);
        }
        $salaires = (float) $requetePaies->sum('montant_net');

        $charges = $depenses + $salaires;

        return [
            'recettes' => round($recettes, 2),
            'depenses' => round($depenses, 2),
            'salaires' => round($salaires, 2),
            'charges' => round($charges, 2),
            'solde' => round($recettes - $charges, 2),
            'taux_charges' => $recettes > 0
                ? round($charges / $recettes * 100, 1)
                : ($charges > 0 ? 100.0 : 0.0),
        ];
    }

    /**
     * Messages d'alerte à afficher (vide si tout va bien).
     *
     * @return array<int, array{niveau: string, message: string}>
     */
    public static function alertes(?AnneeScolaire $annee = null): array
    {
        $etat = self::etat($annee);
        $alertes = [];

        if ($etat['solde'] < 0) {
            $alertes[] = [
                'niveau' => 'danger',
                'message' => 'Trésorerie négative : les charges (dépenses + salaires) dépassent les recettes de '
                    .number_format(abs($etat['solde']), 2, ',', ' ').'.',
            ];
        }

        if ($etat['taux_charges'] >= self::SEUIL_CHARGES) {
            $alertes[] = [
                'niveau' => 'warning',
                'message' => 'Charge élevée : les charges représentent '.$etat['taux_charges'].' % des recettes (seuil d’alerte : '
                    .self::SEUIL_CHARGES.' %).',
            ];
        }

        return $alertes;
    }

    /**
     * Message d'alerte le plus grave à afficher immédiatement après une saisie
     * (null si aucun signal).
     */
    public static function alertePrincipale(?AnneeScolaire $annee = null): ?string
    {
        foreach (self::alertes($annee) as $alerte) {
            if ($alerte['niveau'] === 'danger') {
                return $alerte['message'];
            }
        }

        return null;
    }
}
