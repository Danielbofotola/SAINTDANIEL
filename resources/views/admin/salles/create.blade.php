<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une salle</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .form-label { font-weight: 600; font-size: 13px; }
        .error { color: #dc3545; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-university"></i> Créer une salle</h1>
                <p>Ajoutez une salle utilisable pour les classes.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
        </div>

        <div class="tile p-4">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.salles.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="nom">Nom de la salle *</label>
                    <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required maxlength="50" placeholder="Ex. Salle  A">
                    @error('nom')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacite">Capacité</label>
                    <input class="form-control" id="capacite" name="capacite" type="number" min="1" value="{{ old('capacite') }}" placeholder="Nombre d'élèves">
                    @error('capacite')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="statut">Statut *</label>
                    <select class="form-control" id="statut" name="statut" required>
                        <option value="actif" @selected(old('statut', 'actif') === 'actif')>Active</option>
                        <option value="inactif" @selected(old('statut') === 'inactif')>Inactive</option>
                    </select>
                    @error('statut')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>