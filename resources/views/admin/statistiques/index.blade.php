<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques</title>
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
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-pie-chart"></i> Statistiques</h1>
                <p>Répartitions de l’activité (effectifs, recouvrement, dépenses, recettes)@if ($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a class="btn btn-primary" href="{{ route('admin.statistiques.evolution') }}"><i class="fa fa-line-chart"></i> Évolution mensuelle</a>
                <a class="btn btn-secondary" href="{{ route('admin.comptabilite.index') }}"><i class="fa fa-calculator"></i> Comptabilité</a>
                <a class="btn btn-dark" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-money fa-3x"></i><div class="info"><h4>Recettes</h4><p><b>{{ $money($totaux['recettes']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small danger coloured-icon"><i class="icon fa fa-shopping-cart fa-3x"></i><div class="info"><h4>Dépenses</h4><p><b>{{ $money($totaux['depenses']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-cash-register fa-3x"></i><div class="info"><h4>Salaires</h4><p><b>{{ $money($totaux['salaires']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Élèves inscrits</h4><p><b>{{ $totaux['eleves'] }}</b></p></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-venus-mars"></i> Répartition des élèves par sexe</h4>
                    <canvas id="chartSexe" height="140"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-balance-scale"></i> Recouvrement par classe (défini / encaissé)</h4>
                    <canvas id="chartRecouvrement" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-graduation-cap"></i> Effectifs par classe</h4>
                    <canvas id="chartClasses" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-tags"></i> Dépenses par nature</h4>
                    <canvas id="chartNature" height="180"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-list"></i> Recettes par type de frais</h4>
                    <canvas id="chartTypeFrais" height="180"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tile mb-4 chart-tile">
                    <h4 class="tile-title"><i class="fa fa-credit-card"></i> Moyens de paiement</h4>
                    <canvas id="chartModes" height="180"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') { return; }

            var effectifs = @json($effectifs->map(fn ($classe) => ['nom' => $classe->nom, 'eleves' => $classe->eleves_count])->values());
            var sexes = @json($parSexe);
            var natures = @json($depensesParNature);
            var typesFrais = @json($recettesParType);
            var modes = @json($parMode);
            var recouvrement = @json($recouvrement);

            var palette = ['#0078d4', '#107c41', '#d97706', '#a80000', '#6b21a8', '#0f766e', '#b45309', '#1e40af', '#be185d', '#475569'];

            function camembert(id, libelles, valeurs) {
                var zone = document.getElementById(id);
                if (!zone || libelles.length === 0) { return; }
                new Chart(zone, {
                    type: 'doughnut',
                    data: { labels: libelles, datasets: [{ data: valeurs, backgroundColor: palette }] },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }

            function barres(id, libelles, jeux, horizontal) {
                var zone = document.getElementById(id);
                if (!zone || libelles.length === 0) { return; }
                new Chart(zone, {
                    type: 'bar',
                    data: { labels: libelles, datasets: jeux },
                    options: {
                        responsive: true,
                        indexAxis: horizontal ? 'y' : 'x',
                        scales: { x: { beginAtZero: true }, y: { beginAtZero: true } },
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            camembert('chartSexe', ['Garçons', 'Filles'], [sexes.garcons, sexes.filles]);
            barres('chartClasses', effectifs.map(function (e) { return e.nom; }), [{ label: 'Élèves', data: effectifs.map(function (e) { return e.eleves; }), backgroundColor: '#107c41' }], true);
            barres('chartRecouvrement', recouvrement.map(function (r) { return r.classe; }), [
                { label: 'Défini', data: recouvrement.map(function (r) { return r.defini; }), backgroundColor: '#94a3b8' },
                { label: 'Encaissé', data: recouvrement.map(function (r) { return r.encaisse; }), backgroundColor: '#107c41' }
            ], false);
            camembert('chartNature', Object.keys(natures), Object.values(natures));
            barres('chartTypeFrais', Object.keys(typesFrais), [{ label: 'Recettes', data: Object.values(typesFrais), backgroundColor: '#6b21a8' }], true);
            camembert('chartModes', Object.keys(modes), Object.values(modes));
        })();
    </script>
</body>
</html>
