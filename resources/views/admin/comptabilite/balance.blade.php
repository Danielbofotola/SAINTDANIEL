<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Balance générale OHADA</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="app">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-balance-scale"></i> Balance générale</h1>
                <p>Totaux par compte — débit, crédit et solde (débit − crédit).</p>
            </div>
            <div class="no-print">
                <a class="btn btn-secondary" href="{{ route('admin.comptabilite.index') }}"><i class="fa fa-arrow-left"></i> Comptabilité</a>
                <a class="btn btn-dark" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                <a class="btn btn-info" href="{{ route('admin.comptabilite.plan') }}"><i class="fa fa-sitemap"></i> Plan comptable</a>
                <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Imprimer</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="widget-small primary coloured-icon"><i class="icon fa fa-arrow-up fa-3x"></i><div class="info"><h4>Total débit</h4><p><b>{{ $money($totalDebit) }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small info coloured-icon"><i class="icon fa fa-arrow-down fa-3x"></i><div class="info"><h4>Total crédit</h4><p><b>{{ $money($totalCredit) }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small {{ abs($totalDebit - $totalCredit) < 0.005 ? 'success' : 'danger' }} coloured-icon"><i class="icon fa fa-check fa-3x"></i><div class="info"><h4>Contrôle</h4><p><b>{{ abs($totalDebit - $totalCredit) < 0.005 ? 'Équilibrée' : 'Déséquilibrée' }}</b></p></div></div></div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-list-ol"></i> Synthèse par classe</h4>
            <table class="table table-bordered mb-0">
                <thead><tr><th>Classe</th><th class="text-right">Débit</th><th class="text-right">Crédit</th><th class="text-right">Solde</th></tr></thead>
                <tbody>
                    @forelse ($parClasse as $classe => $ligne)
                        <tr>
                            <td><strong>{{ $classe }}</strong> — {{ $classes[$classe] ?? 'Autres' }}</td>
                            <td class="text-right">{{ $money($ligne['debit']) }}</td>
                            <td class="text-right">{{ $money($ligne['credit']) }}</td>
                            <td class="text-right">{{ $money($ligne['solde']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucun mouvement comptable.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-book"></i> Détail par compte</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead><tr><th>Numéro</th><th>Libellé</th><th>Classe</th><th class="text-right">Débit</th><th class="text-right">Crédit</th><th class="text-right">Solde</th></tr></thead>
                    <tbody>
                        @forelse ($balance as $ligne)
                            <tr>
                                <td><strong>{{ $ligne->numero }}</strong></td>
                                <td>{{ $ligne->libelle }}</td>
                                <td>{{ $ligne->classe }}</td>
                                <td class="text-right">{{ $money($ligne->debit) }}</td>
                                <td class="text-right">{{ $money($ligne->credit) }}</td>
                                <td class="text-right"><strong>{{ $money($ligne->solde) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun mouvement comptable.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="3">Totaux</td>
                            <td class="text-right">{{ $money($totalDebit) }}</td>
                            <td class="text-right">{{ $money($totalCredit) }}</td>
                            <td class="text-right">{{ $money($totalDebit - $totalCredit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
