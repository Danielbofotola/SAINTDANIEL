<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Évolution mensuelle</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        .chart-tile { height: 100%; }
        .chart-tile canvas { width: 100% !important; }
    </style>
</head>
<body class="app">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
@php($solde = round($totaux['recettes'] - $totaux['depenses'] - $totaux['salaires'], 2))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-line-chart"></i> Évolution mensuelle</h1>
                <p>Recettes, dépenses, salaires, solde et inscriptions, mois par mois @if ($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a class="btn btn-primary" href="{{ route('admin.statistiques.index') }}"><i class="fa fa-pie-chart"></i> Statistiques</a>
                <a class="btn btn-secondary" href="{{ route('admin.comptabilite.index') }}"><i class="fa fa-calculator"></i> Comptabilité</a>
                <a class="btn btn-dark" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-money fa-3x"></i><div class="info"><h4>Recettes</h4><p><b>{{ $money($totaux['recettes']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small danger coloured-icon"><i class="icon fa fa-shopping-cart fa-3x"></i><div class="info"><h4>Dépenses</h4><p><b>{{ $money($totaux['depenses']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-cash-register fa-3x"></i><div class="info"><h4>Salaires</h4><p><b>{{ $money($totaux['salaires']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small {{ $solde >= 0 ? 'info' : 'danger' }} coloured-icon"><i class="icon fa fa-balance-scale fa-3x"></i><div class="info"><h4>Solde</h4><p><b>{{ $money($solde) }}</b></p></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-line-chart"></i> Évolution mensuelle — recettes, dépenses, salaires et solde</h4>
                    <canvas id="chartEvolution" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-user-plus"></i> Nouvelles inscriptions par mois</h4>
                    <canvas id="chartInscriptions" height="120"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') { return; }

            var series = @json($series);

            new Chart(document.getElementById('chartEvolution'), {
                type: 'line',
                data: {
                    labels: series.labels,
                    datasets: [
                        { label: 'Recettes', data: series.recettes, borderColor: '#107c41', backgroundColor: 'rgba(16,124,65,.15)', tension: .3, fill: true },
                        { label: 'Dépenses', data: series.depenses, borderColor: '#a80000', backgroundColor: 'rgba(168,0,0,.12)', tension: .3, fill: true },
                        { label: 'Salaires', data: series.salaires, borderColor: '#d97706', backgroundColor: 'rgba(217,119,6,.12)', tension: .3, fill: true },
                        { label: 'Solde', data: series.soldes, borderColor: '#0078d4', borderDash: [6, 4], tension: .3, fill: false }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
            });

            new Chart(document.getElementById('chartInscriptions'), {
                type: 'bar',
                data: { labels: series.labels, datasets: [{ label: 'Inscriptions', data: series.inscriptions, backgroundColor: '#0078d4' }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
            });
        })();
    </script>
</body>
</html>
