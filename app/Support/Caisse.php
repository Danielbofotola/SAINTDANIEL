<?php

namespace App\Support;

use App\Models\AnneeScolaire;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\ParametreSysteme;
use App\Support\Monnaie;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Encaissement d'un paiement : vérifie la cohérence tarif/classe de l'élève,
 * génère un numéro de reçu unique puis enregistre le paiement.
 */
class Caisse
{
    /**
     * @param  array{inscription_id: int|string, frais_scolaire_id: int|string, montant_paye: float|string, mode_paiement: string, periode_id?: int|string|null, reference_transaction?: string|null, date_paiement?: string|null}  $data
     *
     * @throws RuntimeException lorsque le tarif ne correspond pas à la classe de l'élève.
     */
    public static function enregistrer(AnneeScolaire $annee, array $data): Paiement
    {
        $inscription = Inscription::findOrFail($data['inscription_id']);
        $frais = FraisScolaire::findOrFail($data['frais_scolaire_id']);

        if ((int) $frais->classe_id !== (int) $inscription->classe_id) {
            throw new RuntimeException('tarif_classe');
        }

        // Devise du tarif = devise de référence des totaux ; devise encaissée =
        // celle remise par le payeur. Le montant crédité (montant_paye) est
        // toujours ramené dans la devise du tarif au taux appliqué.
        $deviseTarif = Monnaie::normaliser($frais->devise ?? Monnaie::USD);
        $deviseRecue = Monnaie::normaliser($data['devise'] ?? $deviseTarif);
        $taux = (float) ($data['taux'] ?? 0) ?: ParametreSysteme::tauxChange();
        $montantRecu = round((float) $data['montant_paye'], 2);
        $montantPaye = Monnaie::convertir($montantRecu, $deviseRecue, $deviseTarif, $taux);

        $prefix = 'REC-'.$annee->libelle.'-';
        $numero = Paiement::where('num_recu', 'like', $prefix.'%')->count() + 1;
        do {
            $numRecu = $prefix.str_pad((string) $numero++, 4, '0', STR_PAD_LEFT);
        } while (Paiement::where('num_recu', $numRecu)->exists());

        $paiement = Paiement::create(self::colonnesPaiement([
            'num_recu' => $numRecu,
            'inscription_id' => $data['inscription_id'],
            'frais_scolaire_id' => $data['frais_scolaire_id'],
            'periode_id' => $data['periode_id'] ?? null,
            'montant_paye' => $montantPaye,
            'devise' => $deviseRecue,
            'taux' => $taux,
            'montant_recu' => $montantRecu,
            'mode_paiement' => $data['mode_paiement'],
            // Référence automatique : à défaut de saisie, le numéro de reçu fait
            // office de référence de transaction.
            'reference_transaction' => ($data['reference_transaction'] ?? null) ?: $numRecu,
            'date_paiement' => $data['date_paiement'] ?? now(),
            'statut' => 'actif',
        ]));

        // Le règlement du frais d'inscription valide automatiquement l'inscription.
        if ($frais->typeFrais?->estFraisInscription()) {
            $inscription->valider();
        }

        return $paiement;
    }

    /**
     * Écarte les colonnes devise/taux/montant_recu quand elles n'existent pas
     * encore (base non migrée ou schéma de test partiel).
     *
     * @param  array<string, mixed>  $attributs
     * @return array<string, mixed>
     */
    private static function colonnesPaiement(array $attributs): array
    {
        foreach (['devise', 'taux', 'montant_recu'] as $colonne) {
            if (array_key_exists($colonne, $attributs) && ! Schema::hasColumn('paiements', $colonne)) {
                unset($attributs[$colonne]);
            }
        }

        return $attributs;
    }
}
