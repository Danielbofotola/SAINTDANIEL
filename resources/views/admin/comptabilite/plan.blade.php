<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plan comptable OHADA</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-sitemap"></i> Plan comptable SYSCOHADA</h1>
                <p>{{ $comptes->count() }} compte(s) — classes 1 à 9 du référentiel OHADA.</p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.comptabilite.index') }}"><i class="fa fa-arrow-left"></i> Comptabilité</a>
                <a class="btn btn-dark" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                <a class="btn btn-info" href="{{ route('admin.comptabilite.balance') }}"><i class="fa fa-balance-scale"></i> Balance</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Ajouter un compte</h4>
            <form class="row" method="POST" action="{{ route('admin.comptabilite.plan.store') }}">
                @csrf
                <div class="col-md-2 form-group"><label class="form-label">Numéro *</label><input class="form-control" name="numero" value="{{ old('numero') }}" maxlength="10" required placeholder="Ex. 616"></div>
                <div class="col-md-4 form-group"><label class="form-label">Libellé *</label><input class="form-control" name="libelle" value="{{ old('libelle') }}" maxlength="150" required></div>
                <div class="col-md-2 form-group"><label class="form-label">Classe *</label><select class="form-control" name="classe" required>@foreach ($classes as $numero => $nomClasse)<option value="{{ $numero }}" @selected(old('classe') == $numero)>{{ $numero }} — {{ $nomClasse }}</option>@endforeach</select></div>
                <div class="col-md-2 form-group"><label class="form-label">Type *</label><select class="form-control" name="type" required>@foreach ($typesCompte as $valeur => $libelle)<option value="{{ $valeur }}" @selected(old('type') === $valeur)>{{ $libelle }}</option>@endforeach</select></div>
                <div class="col-md-2 form-group d-flex align-items-end"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Ajouter</button></div>
            </form>
        </div>

        @foreach ($comptes->groupBy('classe') as $classe => $liste)
            <div class="tile mb-4">
                <h4 class="tile-title">Classe {{ $classe }} — {{ $classes[$classe] ?? 'Autres' }} <small class="text-muted">({{ $liste->count() }} comptes)</small></h4>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead><tr><th style="width:90px">Numéro</th><th>Libellé</th><th style="width:110px">Type</th><th style="width:130px">Mouvements</th><th style="width:90px"></th></tr></thead>
                        <tbody>
                            @foreach ($liste as $compte)
                                <tr>
                                    <td><strong>{{ $compte->numero }}</strong></td>
                                    <td>{{ $compte->libelle }}</td>
                                    <td><span class="badge badge-secondary">{{ $compte->type }}</span></td>
                                    <td>{{ $compte->lignes_count }} ligne(s)</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.comptabilite.plan.destroy', $compte) }}" onsubmit="return confirm('Supprimer ce compte du plan comptable ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </main>
</body>
</html>
