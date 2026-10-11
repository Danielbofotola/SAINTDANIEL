<?php

namespace App\Support;

use App\Models\AnneeScolaire;
use App\Models\Paiement;
use Illuminate\Support\Facades\Schema;

/**
 * Totaux d'encaissement séparés par devise. Le montant crédité
 * (`paiements.montant_paye`) est déjà exprimé dans la devise du tarif : on le
 * regroupe donc par cette devise pour ne jamais additionner des francs et des
 * dollars ensemble.
 */
class Encaisse
{
    /**
     * Somme encaissée par devise pour une année scolaire.
     *
     * @return array<string, float> ex. ['USD' => 1250.0, 'CDF' => 2_800_000.0]
     */
    public static function parDevise(?AnneeScolaire $annee): array
    {
        if ($annee === null) {
            return [];
        }

        $query = Paiement::query()
            ->where('paiements.statut', 'actif')
            ->whereHas('inscription', fn ($inscription) => $inscription->where('annee_scolaire_id', $annee->id));

        // Sans la colonne devise (base non migrée ou schéma de test partiel),
        // tout est considéré en dollar.
        if (! Schema::hasColumn('frais_scolaires', 'devise')) {
            return [Monnaie::USD => (float) $query->sum('montant_paye')];
        }

        return $query
            ->join('frais_scolaires', 'frais_scolaires.id', '=', 'paiements.frais_scolaire_id')
            ->selectRaw('frais_scolaires.devise as devise, SUM(paiements.montant_paye) as total')
            ->groupBy('frais_scolaires.devise')
            ->pluck('total', 'devise')
            ->mapWithKeys(fn ($total, $devise) => [Monnaie::normaliser($devise) => (float) $total])
            ->all();
    }

    /**
     * Idem, pour les montants exigés (frais scolaires) par devise.
     *
     * @return array<string, float>
     */
    public static function exigeParDevise(?AnneeScolaire $annee): array
    {
        if ($annee === null) {
            return [];
        }

        $query = \App\Models\FraisScolaire::query()
            ->where('annee_scolaire_id', $annee->id)
            ->where('statut', 'actif');

        if (! Schema::hasColumn('frais_scolaires', 'devise')) {
            return [Monnaie::USD => (float) $query->sum('montant_exige')];
        }

        return $query
            ->selectRaw('devise, SUM(montant_exige) as total')
            ->groupBy('devise')
            ->pluck('total', 'devise')
            ->mapWithKeys(fn ($total, $devise) => [Monnaie::normaliser($devise) => (float) $total])
            ->all();
    }
}
