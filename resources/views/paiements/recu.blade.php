<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reçu {{ $paiement->num_recu }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#eef2f6; font-family:'Segoe UI', system-ui, sans-serif; color:#1e293b; }
        .recu { max-width:760px; margin:24px auto; background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:28px; box-shadow:0 4px 14px rgba(0,0,0,.08); }
        .recu-head { border-bottom:2px solid #0f172a; padding-bottom:12px; margin-bottom:16px; }
        .num-recu { font-family:'Consolas', monospace; font-size:1.25rem; font-weight:700; letter-spacing:.5px; }
        .table-lignes th { background:#f1f5f9; }
        @media print { .no-print { display:none !important; } body { background:#fff; } .recu { margin:0; border:none; box-shadow:none; } }
    </style>
</head>
<body>
@php($money = fn ($v, $d = 'USD') => \App\Support\Monnaie::format($v, $d))
@php($deviseFrais = \App\Support\Monnaie::normaliser($paiement->fraisScolaire?->devise))
<div class="recu">
    <div class="recu-head row">
        <div class="col-8">
            <div class="fw-bold fs-4">{{ $systemSettings->nom_etablissement }}</div>
            <div class="text-muted small">Reçu officiel de paiement des frais scolaires</div>
        </div>
        <div class="col-4 text-end">
            <div class="text-muted small text-uppercase">N° de reçu</div>
            <div class="num-recu">{{ $paiement->num_recu }}</div>
            <div class="text-muted small">Généré automatiquement</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="fw-bold">Élève</div>
            <div>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->postnom }} {{ $paiement->inscription?->eleve?->prenom }}</div>
            <div class="text-muted small">Matricule : {{ $paiement->inscription?->eleve?->matricule ?? '—' }} · Classe : {{ $paiement->inscription?->classe?->nom ?? '—' }}</div>
        </div>
        <div class="col-6 text-end">
            <div class="fw-bold">Détails</div>
            <div>Date : {{ $paiement->date_paiement?->format('d/m/Y H:i') }}</div>
            <div>Année scolaire : {{ $paiement->inscription?->anneeScolaire?->libelle ?? '—' }}</div>
            <div>Période : {{ $paiement->periode?->nom ?? 'Non attribuée' }}</div>
        </div>
    </div>

    <table class="table table-bordered table-lignes align-middle">
        <thead>
            <tr><th>Désignation</th><th class="text-end">Montant</th></tr>
        </thead>
        <tbody>
            <tr><td>{{ $paiement->fraisScolaire?->typeFrais?->nom ?? 'Frais scolaires' }}@if ($paiement->fraisScolaire?->classe) — {{ $paiement->fraisScolaire->classe->nom }}@endif</td><td class="text-end">{{ $money($exige, $deviseFrais) }}</td></tr>
            <tr><td class="fw-bold">Montant versé (ce reçu)</td><td class="text-end fw-bold">{{ $money($paiement->montant_paye, $deviseFrais) }}</td></tr>
            @if (\App\Support\Monnaie::normaliser($paiement->deviseRecue()) !== $deviseFrais)
                <tr class="text-muted"><td>Encaissé en {{ $deviseFrais === 'CDF' ? 'dollars' : 'francs congolais' }}</td>
                    <td class="text-end">{{ $money($paiement->montantRecu(), $paiement->deviseRecue()) }}
                        <span class="small">· taux {{ number_format((float) $paiement->taux, 2, ',', ' ') }} FC/1$</span></td></tr>
            @endif
        </tbody>
    </table>

    <div class="row g-2 mb-3">
        <div class="col-4"><div class="border rounded p-2 text-center"><div class="text-muted small">Total déjà payé</div><div class="fw-bold">{{ $money($paye, $deviseFrais) }}</div></div></div>
        <div class="col-4"><div class="border rounded p-2 text-center"><div class="text-muted small">Reste à payer</div><div class="fw-bold {{ $reste <= 0 ? 'text-success' : 'text-danger' }}">{{ $money($reste, $deviseFrais) }}</div></div></div>
        <div class="col-4"><div class="border rounded p-2 text-center"><div class="text-muted small">Statut</div><div class="fw-bold">{{ $reste <= 0 ? 'Soldé' : 'En cours' }}</div></div></div>
    </div>

    <div class="row text-muted small">
        <div class="col-6">Mode de paiement : <strong class="text-dark">{{ ['especes' => 'Espèces', 'banque' => 'Banque', 'mobile_money' => 'Mobile Money'][$paiement->mode_paiement] ?? $paiement->mode_paiement }}</strong></div>
        <div class="col-6 text-end">Référence : <strong class="text-dark">{{ $paiement->reference_transaction ?: '—' }}</strong></div>
    </div>

    <div class="row mt-5">
        <div class="col-6"><div class="border-top pt-1 small text-muted">Signature du caissier</div></div>
        <div class="col-6 text-end"><div class="border-top pt-1 small text-muted">Signature du parent / tuteur</div></div>
    </div>

    <div class="text-center mt-4 no-print">
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimer le reçu</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Retour</a>
    </div>
</div>
</body>
</html>
