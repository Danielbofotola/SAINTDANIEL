<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\ParametreSysteme;
use Illuminate\View\View;

/**
 * Reçu d'un paiement : document imprimable reprenant l'école, l'élève, le tarif,
 * la période, le montant encaissé et le reste à payer.
 */
class RecuController extends Controller
{
    public function show(Paiement $paiement): View
    {
        // Module optionnel : la page « reçu » peut être désactivée.
        abort_unless(ParametreSysteme::recusActifs(), 404);

        $paiement->load([
            'inscription.eleve', 'inscription.classe', 'inscription.anneeScolaire',
            'fraisScolaire.typeFrais', 'periode',
        ]);

        $exige = (float) ($paiement->fraisScolaire?->montant_exige ?? 0);

        $paye = (float) Paiement::query()
            ->where('statut', 'actif')
            ->where('inscription_id', $paiement->inscription_id)
            ->where('frais_scolaire_id', $paiement->frais_scolaire_id)
            ->sum('montant_paye');

        return view('paiements.recu', [
            'paiement' => $paiement,
            'exige' => $exige,
            'paye' => $paye,
            'reste' => max($exige - $paye, 0),
        ]);
    }
}
