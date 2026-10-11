<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une option</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
       body { min-height: 100vh; background: #f2f5f2; color: #263a36; }
       .page-top { padding: 16px 24px; color: #fff; background: #193c39; }
       .page-top a { color: #fff; }
       .form-wrap { width: min(680px, calc(100% - 32px)); margin: 36px auto; }
       .form-heading { margin-bottom: 19px; }
       .eyebrow { margin-bottom: 7px; color: #71817d; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
       h1 { margin: 0; color: #193c39; font-size: 27px; font-weight: 800; }
       .form-heading p { margin: 7px 0 0; color: #71817d; font-size: 13px; }
       .form-panel { padding: 25px; background: #fff; border: 1px solid #e1e8e2; border-radius: 10px; box-shadow: 0 10px 28px rgba(25,60,57,.06); }
       .form-label { color: #304743; font-size: 12px; font-weight: 700; }
       .form-control { min-height: 43px; border-color: #dbe4dc; }
       .form-control:focus { border-color: #6e9b82; box-shadow: 0 0 0 3px rgba(110,155,130,.14); }
       .hint { margin-top: 7px; color: #71817d; font-size: 11px; }
       .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 22px; }
       @media (max-width: 520px) { .page-top { padding: 14px 16px; } .form-wrap { margin-top: 24px; } .form-panel { padding: 19px; } h1 { font-size: 23px; } }
    </style>
</head>
<body>
    <header class="page-top d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.classes.create') }}"><i class="fa fa-arrow-left"></i> Création d’une classe</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Déconnexion</button></form>
    </header>
    <main class="form-wrap">
        <div class="form-heading"><p class="eyebrow">Organisation scolaire</p><h1>Créer une option</h1><p>L’option sera rattachée à la section choisie.</p></div>
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if ($sections->isEmpty())
            <div class="alert alert-warning">Aucune section n’existe encore. <a href="{{ route('admin.sections.create') }}">Créez d’abord une section</a>.</div>
        @endif
        <section class="form-panel">
            <form method="POST" action="{{ route('admin.options.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="section_id">Section *</label>
                    <select class="form-control" id="section_id" name="section_id" required>
                        <option value="">Sélectionner une section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected((string) old('section_id', $selectedSectionId) === (string) $section->id)>{{ $section->nom }}</option>
                        @endforeach
                    </select>
                    @error('section_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nom">Nom de l’option *</label>
                    <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}" maxlength="100" required placeholder="Ex. Latin-Mathématiques">
                    @error('nom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="hint">Après création, vous serez ramené au formulaire de classe.</div>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="{{ route('admin.classes.create') }}">Annuler</a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.options.index') }}">Gérer les options</a>
                    <button class="btn btn-primary" type="submit" @disabled($sections->isEmpty())><i class="fa fa-check"></i> Créer l’option</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
