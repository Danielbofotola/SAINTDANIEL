<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Options</title>
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
                <h1><i class="fa fa-list-ul"></i> Options</h1>
                <p>Créez les options de chaque section et consultez les classes concernées.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-primary"><i class="fa fa-sitemap"></i> Gérer les sections</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($sections->isEmpty())
            <div class="alert alert-warning">
                Aucune section n'existe encore. <a href="{{ route('admin.sections.index') }}">Créez d'abord une section</a> pour pouvoir y rattacher une option.
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="tile p-4">
                    <h5 class="mb-3">Nouvelle option</h5>
                    <form method="POST" action="{{ route('admin.options.store') }}">
                        @csrf
                        <input type="hidden" name="retour" value="gestion">

                        <div class="form-group">
                            <label class="form-label" for="section_id">Section *</label>
                            <select class="form-control" id="section_id" name="section_id" required>
                                <option value="">Sélectionner une section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" @selected((string) old('section_id', $selectedSectionId) === (string) $section->id)>{{ $section->nom }}</option>
                                @endforeach
                            </select>
                            @error('section_id')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nom">Nom de l'option *</label>
                            <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}"
                                   required maxlength="100" placeholder="Ex. Latin-Mathématiques">
                            @error('nom')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" @disabled($sections->isEmpty())>
                            <i class="fa fa-plus"></i> Créer l'option
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="tile p-4">
                    <h5 class="mb-3">Options enregistrées</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Option</th>
                                    <th>Section</th>
                                    <th class="text-center">Classes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($options as $option)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $option->nom }}</strong></td>
                                        <td>{{ $option->section?->nom ?? 'Section supprimée' }}</td>
                                        <td class="text-center">{{ $option->classes_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="4">Aucune option enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0"><small><i class="fa fa-info-circle"></i> Le nom d'une option doit être unique au sein de sa section.</small></p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
