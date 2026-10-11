<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques administrateur</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { background: #f4f6f4; color: #263a36; }
        .stats-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 26px; color: #fff; background: #193c39; }
        .stats-brand { display: flex; align-items: center; gap: 12px; }
        .stats-mark { display: grid; width: 40px; height: 40px; place-items: center; color: #193c39; background: #c8ef74; border-radius: 12px 12px 12px 4px; font-size: 17px; font-weight: 800; }
        .stats-brand strong { display: block; font-family: Arial, sans-serif; font-size: 14px; letter-spacing: .3px; }
        .stats-brand small { display: block; margin-top: 3px; color: #bfd1c9; font-size: 10px; }
        .stats-nav { display: flex; flex-wrap: wrap; gap: 5px; padding: 9px 26px; background: #28514b; }
        .stats-nav a { padding: 8px 11px; color: #e8f1eb; border-radius: 5px; font-size: 12px; font-weight: 600; }
        .stats-nav a:hover, .stats-nav a.active { color: #193c39; background: #c8ef74; text-decoration: none; }
        .stats-content { width: min(1120px, calc(100% - 40px)); margin: 0 auto; padding: 34px 0 48px; }
        .stats-title { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 25px; }
        .stats-eyebrow { margin: 0 0 7px; color: #6f8179; font-size: 10px; font-weight: 700; letter-spacing: 1.3px; text-transform: uppercase; }
        h1 { margin: 0; color: #193c39; font-family: 'Manrope', 'Segoe UI', sans-serif; font-size: 27px; font-weight: 800; }
        .stats-subtitle { margin: 7px 0 0; color: #71817d; font-size: 13px; }
        .stats-date { color: #667b73; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .chart-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; }
        .chart-panel { min-width: 0; padding: 21px; background: #fff; border: 1px solid #e1e8e2; border-radius: 10px; box-shadow: 0 10px 26px rgba(25, 60, 57, .055); }
        .chart-heading { margin-bottom: 16px; }
        .chart-heading h2 { margin: 0; color: #193c39; font-size: 15px; font-weight: 700; }
        .chart-heading p { margin: 5px 0 0; color: #7a8983; font-size: 11px; }
        .chart-wrap { position: relative; height: 300px; }
        .stats-footer { margin-top: 18px; color: #899791; font-size: 10px; }
        @media (max-width: 720px) {
            .stats-header { padding: 15px 17px; }
            .stats-nav { padding: 8px 12px; }
            .stats-content { width: min(100% - 28px, 540px); padding-top: 25px; }
            .stats-title { align-items: flex-start; flex-direction: column; gap: 8px; }
            h1 { font-size: 23px; }
            .chart-grid { grid-template-columns: minmax(0, 1fr); }
            .chart-panel { padding: 17px; }
            .chart-wrap { height: 270px; }
        }
    </style>
</head>
<body>
    <header class="stats-header">
        <a class="stats-brand text-white" href="{{ route('admin.profile.show') }}">
            <span class="stats-mark">M</span>
            <span><strong>{{ $systemSettings->nom_court }}</strong><small>Espace administrateur</small></span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit"><i class="fa fa-sign-out"></i> Déconnexion</button></form>
    </header>
    <nav class="stats-nav" aria-label="Navigation du profil administrateur">
        <a href="{{ route('admin.profile.show') }}"><i class="fa fa-user"></i> Mon profil</a>
        <a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Comptes</a>
        <a class="active" href="{{ route('admin.profile.statistics') }}" aria-current="page"><i class="fa fa-bar-chart"></i> Statistiques</a>
        <a href="{{ route('admin.profile.show') }}#password"><i class="fa fa-key"></i> Sécurité du compte</a>
        <a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
    </nav>
    <main class="stats-content">
        <section class="stats-title">
            <div><p class="stats-eyebrow">Vue d’ensemble de l’établissement</p><h1>Statistiques</h1><p class="stats-subtitle">Répartition des comptes et des ressources actives.</p></div>
            <time class="stats-date" datetime="{{ now()->toDateString() }}"><i class="fa fa-calendar-o"></i> {{ now()->format('d/m/Y') }}</time>
        </section>
        <div class="chart-grid">
            <section class="chart-panel">
                <div class="chart-heading"><h2>Comptes par rôle</h2><p>Effectif des utilisateurs selon leur profil</p></div>
                <div class="chart-wrap"><canvas id="administrationChart" role="img" aria-label="Graphique du nombre de comptes par rôle"></canvas></div>
            </section>
            <section class="chart-panel">
                <div class="chart-heading"><h2>Ressources scolaires</h2><p>Nombre d’éléments actuellement actifs</p></div>
                <div class="chart-wrap"><canvas id="resourcesChart" role="img" aria-label="Graphique des ressources scolaires actives"></canvas></div>
            </section>
        </div>
        <p class="stats-footer">Dernière actualisation : {{ now()->format('d/m/Y à H:i') }}</p>
    </main>
    <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>
    <script>
        new Chart(document.getElementById('administrationChart'), {
            type: 'bar',
            data: {
                labels: @json($administrationStats['labels']),
                datasets: [{ label: 'Comptes utilisateurs', data: @json($administrationStats['roles']), backgroundColor: ['#193c39', '#648fc6', '#d8735a', '#aacd57', '#c5a84c'], borderRadius: 5, maxBarThickness: 48 }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf1ed' } } }, plugins: { legend: { display: false } } }
        });
        new Chart(document.getElementById('resourcesChart'), {
            type: 'doughnut',
            data: {
                labels: ['Élèves', 'Classes', 'Professeurs', 'Salles'],
                datasets: [{ data: @json($administrationStats['modules']), backgroundColor: ['#648fc6', '#193c39', '#aacd57', '#d8735a'], borderWidth: 3, borderColor: '#fff', hoverOffset: 7 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18 } } } }
        });
    </script>
</body>
</html>
