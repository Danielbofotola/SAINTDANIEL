<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Année clôturée — {{ $annee?->libelle }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="app">
@php($fmt = fn ($v) => number_format((float) $v, 2, ',', ' '))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-archive"></i> Année clôturée — {{ $annee?->libelle ?? '—' }}</h1>
                <p>Consultation en lecture seule. Clôturée le <strong>{{ $archive->date_cloture?->format('d/m/Y H:i') }}</strong>@if ($archive->remarques) · {{ $archive->remarques }}@endif</p>
            </div>
            <div class="no-print">
                <a href="{{ route('admin.archives.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Retour aux archives</a>
                <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Imprimer</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Élèves inscrits</h4><p><b>{{ $totalEleves }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-money fa-3x"></i><div class="info"><h4>Recettes</h4><p><b>{{ $fmt($archive->total_recettes) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small danger coloured-icon"><i class="icon fa fa-shopping-cart fa-3x"></i><div class="info"><h4>Dépenses</h4><p><b>{{ $fmt($archive->total_depenses) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-balance-scale fa-3x"></i><div class="info"><h4>Solde</h4><p><b>{{ $fmt($archive->solde_final) }}</b></p></div></div></div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-lock"></i> Chiffres figés à la clôture</h4>
            <div class="row">
                <div class="col-md-3"><strong>Frais attendus :</strong> {{ $fmt($archive->total_attendu) }}</div>
                <div class="col-md-3"><strong>Impayés :</strong> <span class="text-danger">{{ $fmt($archive->total_impayes) }}</span></div>
                <div class="col-md-3"><strong>Effectif :</strong> {{ $archive->eleves_garcons }} garçon(s) · {{ $archive->eleves_filles }} fille(s)</div>
                <div class="col-md-3"><strong>Résultats :</strong> {{ $archive->total_bulletins }} bulletin(s) · {{ $archive->total_notes }} note(s)</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3"><strong>Recettes :</strong> {{ $fmt($archive->total_recettes) }}</div>
                <div class="col-md-3"><strong>Dépenses :</strong> {{ $fmt($archive->total_depenses) }}</div>
                <div class="col-md-3"><strong>Salaires versés :</strong> <span class="text-danger">{{ $fmt($archive->total_salaires) }}</span> <small class="text-muted">({{ $archive->nombre_paies }} paie(s))</small></div>
                <div class="col-md-3"><strong>Solde :</strong> {{ $fmt($archive->solde_final) }} <small class="text-muted">recettes − dépenses − salaires</small></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-graduation-cap"></i> Effectifs par classe</h4>
                    <table class="table table-hover table-bordered mb-0">
                        <thead><tr><th>Classe</th><th class="text-right">Élèves</th></tr></thead>
                        <tbody>
                            @forelse ($effectifs as $classe)
                                <tr><td>{{ $classe->nom }}</td><td class="text-right">{{ $classe->eleves_count }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Aucune inscription archivée.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($effectifs->isNotEmpty())
                            <tfoot><tr class="font-weight-bold"><td>Total</td><td class="text-right">{{ $totalEleves }}</td></tr></tfoot>
                        @endif
                    </table>
                    <p class="text-muted mb-0 mt-2">Répartition : <strong>{{ $garcons }}</strong> garçon(s) · <strong>{{ $filles }}</strong> fille(s).</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-money"></i> Recettes par type de frais</h4>
                    <table class="table table-bordered mb-0">
                        <tbody>
                            @forelse ($recettesParType as $type => $montant)
                                <tr><td>{{ $type }}</td><td class="text-right">{{ $fmt($montant) }}</td></tr>
                            @empty
                                <tr><td class="text-center text-muted">Aucun paiement.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($recettesParType->isNotEmpty())
                            <tfoot><tr class="font-weight-bold"><td>Total</td><td class="text-right">{{ $fmt($archive->total_recettes) }}</td></tr></tfoot>
                        @endif
                    </table>
                    <hr>
                    <h5>Par mode de paiement</h5>
                    <ul class="list-unstyled mb-0">
                        @forelse ($recettesParMode as $mode => $montant)
                            <li class="d-flex justify-content-between border-bottom py-1"><span>{{ $modes[$mode] ?? ucfirst($mode) }}</span><strong>{{ $fmt($montant) }}</strong></li>
                        @empty
                            <li class="text-muted">Aucun paiement.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-shopping-cart"></i> Dépenses par catégorie</h4>
            <table class="table table-bordered mb-0">
                <tbody>
                    @forelse ($depensesParCategorie as $categorie => $montant)
                        <tr><td>{{ $categorie }}</td><td class="text-right">{{ $fmt($montant) }}</td></tr>
                    @empty
                        <tr><td class="text-center text-muted">Aucune dépense.</td></tr>
                    @endforelse
                </tbody>
                @if ($depensesParCategorie->isNotEmpty())
                    <tfoot><tr class="font-weight-bold"><td>Total</td><td class="text-right">{{ $fmt($archive->total_depenses) }}</td></tr></tfoot>
                @endif
            </table>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-credit-card"></i> Paiements ({{ $paiements->total() }})</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Reçu</th><th>Date</th><th>Élève</th><th>Classe</th><th>Type de frais</th><th class="text-right">Montant</th><th>Mode</th></tr></thead>
                    <tbody>
                        @forelse ($paiements as $paiement)
                            <tr>
                                <td>{{ $paiement->num_recu }}</td>
                                <td>{{ $paiement->date_paiement?->format('d/m/Y') }}</td>
                                <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}</td>
                                <td>{{ $paiement->inscription?->classe?->nom }}</td>
                                <td>{{ $paiement->fraisScolaire?->typeFrais?->nom }}</td>
                                <td class="text-right">{{ $fmt($paiement->montant_paye) }}</td>
                                <td>{{ $modes[$paiement->mode_paiement] ?? $paiement->mode_paiement }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Aucun paiement archivé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $paiements->links('pagination::bootstrap-4') }}
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-shopping-cart"></i> Dépenses ({{ $depenses->total() }})</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Date</th><th>Catégorie</th><th>Motif</th><th class="text-right">Montant</th></tr></thead>
                    <tbody>
                        @forelse ($depenses as $depense)
                            <tr>
                                <td>{{ $depense->date_depense?->format('d/m/Y') }}</td>
                                <td>{{ $depense->categorie?->nom }}</td>
                                <td>{{ $depense->motif }}</td>
                                <td class="text-right">{{ $fmt($depense->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucune dépense archivée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $depenses->links('pagination::bootstrap-4') }}
        </div>
    </main>
</body>
</html>
