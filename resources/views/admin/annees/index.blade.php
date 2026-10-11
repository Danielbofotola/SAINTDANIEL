<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Années scolaires</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <style>
        .form-label { font-weight: 600; font-size: 13px; }
        .error { color: #dc3545; font-size: 12px; margin-top: 4px; }

        /* Interrupteur d'activation de l'année en cours */
        .switch { position: relative; display: inline-block; width: 46px; height: 24px; margin: 0; vertical-align: middle; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch .slider { position: absolute; cursor: pointer; inset: 0; background-color: #cfd8dc; transition: .2s; border-radius: 24px; }
        .switch .slider::before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; transition: .2s; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,.3); }
        .switch input:checked + .slider { background-color: #28a745; }
        .switch input:checked + .slider::before { transform: translateX(22px); }
        .switch input:focus + .slider { box-shadow: 0 0 1px #28a745; }
        .annee-row-current { background-color: #f1fbf3; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-calendar"></i> Années scolaires</h1>
                <p>Créez les années scolaires et définissez celle qui est en cours.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="tile p-4">
                    <h5 class="mb-3">Nouvelle année</h5>
                    <form method="POST" action="{{ route('admin.annees.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="libelle">Libellé *</label>
                            <input class="form-control" id="libelle" name="libelle" value="{{ old('libelle') }}"
                                   required maxlength="20" placeholder="Ex. 2025-2026">
                            @error('libelle')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="date_debut">Date de début *</label>
                            <input class="form-control" id="date_debut" name="date_debut" type="text" data-date-fr
                                   value="{{ old('date_debut') }}" required>
                            @error('date_debut')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="date_fin">Date de fin *</label>
                            <input class="form-control" id="date_fin" name="date_fin" type="text" data-date-fr
                                   value="{{ old('date_fin') }}" required>
                            @error('date_fin')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-plus"></i> Créer l'année
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="tile p-4">
                    <h5 class="mb-3">Années enregistrées</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Libellé</th>
                                    <th>Période</th>
                                    <th>En cours</th>
                                    <th class="text-center">Activer / Désactiver</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($annees as $annee)
                                    <tr class="{{ $annee->is_encours ? 'annee-row-current' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $annee->libelle }}</td>
                                        <td>
                                            {{ \Illuminate\Support\Carbon::parse($annee->date_debut)->format('d/m/Y') }}
                                            &rarr;
                                            {{ \Illuminate\Support\Carbon::parse($annee->date_fin)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            @if ($annee->is_encours)
                                                <span class="badge badge-success">En cours</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.annees.toggle', $annee) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <label class="switch" title="{{ $annee->is_encours ? 'Désactiver' : 'Définir comme année en cours' }}">
                                                    <input type="checkbox" onchange="this.form.submit()" @checked($annee->is_encours)>
                                                    <span class="slider"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.annees.edit', $annee) }}" class="btn btn-sm btn-info" title="Modifier"><i class="fa fa-pencil"></i></a>
                                            <form method="POST" action="{{ route('admin.annees.destroy', $annee) }}" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment supprimer cette année scolaire ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="6">Aucune année scolaire enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0"><small><i class="fa fa-info-circle"></i> Une seule année peut être « en cours » : activer une année désactive automatiquement les autres.</small></p>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
