<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flux académique</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <style>
        .switch-row { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid #e5ebe5; border-radius: 8px; }
        .switch-row input[type=checkbox] { width: 20px; height: 20px; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-toggle-on"></i> Flux académique</h1>
                <p>Ouvrez ou fermez l'encodage des points par les enseignants — pour garantir l'intégrité des délibérations.</p>
            </div>
            <div>
                <a href="{{ route('prefecture.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('prefecture.decisions.index') }}" class="btn btn-primary"><i class="fa fa-gavel"></i> Décisions &amp; injonctions</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="tile">
            <form method="POST" action="{{ route('prefecture.flux.update') }}">
                @csrf @method('PUT')
                <div class="switch-row">
                    <input type="checkbox" id="saisie_notes_active" name="saisie_notes_active" value="1" @checked($parametres->saisie_notes_active)>
                    <label for="saisie_notes_active" class="mb-0">
                        <strong>Encodage des points ouvert</strong>
                        <div class="text-muted small">Décoché : les enseignants ne peuvent plus saisir de notes (période fermée pour délibération).</div>
                    </label>
                </div>
                <div class="mt-3"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Appliquer</button></div>
            </form>
        </div>
    </main>
</body>
</html>
