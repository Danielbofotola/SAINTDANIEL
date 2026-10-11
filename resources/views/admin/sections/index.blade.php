<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sections</title>
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
                <h1><i class="fa fa-sitemap"></i> Sections</h1>
                <p>Créez les sections et consultez les options et classes qui y sont rattachées.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.options.index') }}" class="btn btn-outline-primary"><i class="fa fa-list"></i> Gérer les options</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="tile p-4">
                    <h5 class="mb-3">Nouvelle section</h5>
                    <form method="POST" action="{{ route('admin.sections.store') }}">
                        @csrf
                        <input type="hidden" name="retour" value="gestion">

                        <div class="form-group">
                            <label class="form-label" for="nom">Nom de la section *</label>
                            <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}"
                                   required maxlength="100" placeholder="Ex. Scientifique">
                            @error('nom')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-plus"></i> Créer la section
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="tile p-4">
                    <h5 class="mb-3">Sections enregistrées</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Section</th>
                                    <th class="text-center">Options</th>
                                    <th class="text-center">Classes</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sections as $section)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $section->nom }}</strong></td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.options.index', ['section_id' => $section->id]) }}" class="badge badge-info">{{ $section->options_count }} option(s)</a>
                                        </td>
                                        <td class="text-center">{{ $section->classes_count }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.options.index', ['section_id' => $section->id]) }}" class="btn btn-sm btn-info" title="Voir les options"><i class="fa fa-list"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="5">Aucune section enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0"><small><i class="fa fa-info-circle"></i> Une section regroupe plusieurs options ; une classe se rattache à une section et, si besoin, à une option.</small></p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
