<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une année scolaire</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <style>
        .form-label { font-weight: 600; font-size: 13px; }
        .error { color: #dc3545; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-calendar"></i> Modifier une année scolaire</h1>
                <p>Mettez à jour le libellé et la période de l'année « {{ $annee->libelle }} ».</p>
            </div>
            <a href="{{ route('admin.annees.index') }}" class="btn btn-secondary">Retour</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Modification impossible :</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="tile p-4">
            <form method="POST" action="{{ route('admin.annees.update', $annee) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="libelle">Libellé *</label>
                    <input class="form-control" id="libelle" name="libelle" value="{{ old('libelle', $annee->libelle) }}"
                           required maxlength="20" placeholder="Ex. 2025-2026">
                    @error('libelle')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="date_debut">Date de début *</label>
                        <input class="form-control" id="date_debut" name="date_debut" type="text" data-date-fr
                               value="{{ old('date_debut', $annee->date_debut?->format('Y-m-d')) }}" required>
                        @error('date_debut')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="date_fin">Date de fin *</label>
                        <input class="form-control" id="date_fin" name="date_fin" type="text" data-date-fr
                               value="{{ old('date_fin', $annee->date_fin?->format('Y-m-d')) }}" required>
                        @error('date_fin')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if ($annee->is_encours)
                    <div class="alert alert-info py-2"><i class="fa fa-info-circle"></i> Cette année est actuellement l'année en cours.</div>
                @endif

                <div class="text-right">
                    <a href="{{ route('admin.annees.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
