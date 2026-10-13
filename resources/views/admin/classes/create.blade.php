<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($classe) ? 'Modifier une classe' : 'Créer une classe' }}</title>
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
    @php($selectedSectionId = old('section_id', request('section_id', $classe->section_id ?? null)))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-graduation-cap"></i> {{ isset($classe) ? 'Modifier une classe' : 'Créer une classe' }}</h1>
                <p>Associez la classe à un cycle et, si nécessaire, à une salle.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
        </div>

        <div class="tile p-4">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Impossible d'enregistrer la classe :</strong>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if ($cycles->isEmpty())
                <div class="alert alert-warning">Créez d'abord un cycle actif avant d'ajouter une classe.</div>
            @endif

            <form method="POST" action="{{ isset($classe) ? route('admin.classes.update', $classe) : route('admin.classes.store') }}">
                @csrf
                @if (isset($classe)) @method('PUT') @endif
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="cycle_id">Cycle *</label>
                        <select class="form-control" id="cycle_id" name="cycle_id" required>
                            <option value="">Sélectionner un cycle</option>
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected(old('cycle_id', $classe->cycle_id ?? null) == $cycle->id)>{{ $cycle->nom }}</option>
                            @endforeach
                        </select>
                        @error('cycle_id')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="nom">Nom de la classe *</label>
                        <input class="form-control" id="nom" name="nom" value="{{ old('nom', $classe->nom ?? '') }}" required maxlength="50" placeholder="Ex. 1ère A">
                        @error('nom')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="ordre">Niveau (ordre de progression)</label>
                        <input type="number" class="form-control" id="ordre" name="ordre" min="1" max="30"
                               value="{{ old('ordre', $classe->ordre ?? '') }}" placeholder="Ex. 1, 2, 3…">
                        <small class="text-muted">
                            Utilisé par la délibération : la classe suivante est celle du niveau
                            immédiatement supérieur, dans le même cycle.
                        </small>
                        @error('ordre')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="salle_id">Salle</label>
                        <select class="form-control" id="salle_id" name="salle_id">
                            <option value="">Aucune salle attribuée</option>
                            @foreach ($salles as $salle)
                                <option value="{{ $salle->id }}" @selected(old('salle_id', $classe->salle_id ?? null) == $salle->id)>{{ $salle->nom }}{{ $salle->capacite ? ' - '.$salle->capacite.' places' : '' }}</option>
                            @endforeach
                        </select>
                        @if ($salles->isEmpty())<small class="text-muted">Aucune salle libre actuellement.</small>@endif
                        @error('salle_id')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="classe_suivante_id">Classe suivante (passage)</label>
                        <select class="form-control" id="classe_suivante_id" name="classe_suivante_id">
                            <option value="">Déduite du niveau (ordre suivant, même cycle)</option>
                            @foreach ($classesSuivantes as $candidat)
                                <option value="{{ $candidat->id }}" @selected(old('classe_suivante_id', $classe->classe_suivante_id ?? null) == $candidat->id)>{{ $candidat->nom }}{{ $candidat->cycle ? ' — '.$candidat->cycle->nom : '' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Force la classe de destination en fin d'année — utile pour changer de cycle
                            (ex. 6e primaire → 1re secondaire). Vide = niveau immédiatement supérieur.
                        </small>
                        @error('classe_suivante_id')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <div class="d-flex justify-content-between align-items-center"><label class="form-label mb-2" for="section_id">Section</label><a class="btn btn-sm btn-outline-primary mb-2" href="{{ route('admin.sections.index') }}" target="_blank" rel="noopener"><i class="fa fa-plus"></i> Nouvelle section</a></div>
                        <select class="form-control" id="section_id" name="section_id">
                            <option value="">Aucune section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected($selectedSectionId == $section->id)>{{ $section->nom }}</option>
                            @endforeach
                        </select>
                        @error('section_id')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <div class="d-flex justify-content-between align-items-center"><label class="form-label mb-2" for="option_id">Option</label><a class="btn btn-sm btn-outline-primary mb-2" id="create-option-link" href="{{ route('admin.options.index', ['section_id' => old('section_id', request('section_id', $classe->section_id ?? null))]) }}" target="_blank" rel="noopener"><i class="fa fa-plus"></i> Nouvelle option</a></div>
                        <select class="form-control" id="option_id" name="option_id">
                            <option value="">Aucune option</option>
                            @foreach ($options as $option)
                                <option value="{{ $option->id }}" data-section-id="{{ $option->section_id }}" @if (!$selectedSectionId || $option->section_id != $selectedSectionId) hidden disabled @endif @selected(old('option_id', request('option_id', $classe->option_id ?? null)) == $option->id)>{{ $option->nom }}</option>
                            @endforeach
                        </select>
                        @error('option_id')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 form-group">
                        <label class="form-label" for="destinations">Destinations multiples (passage à choix)</label>
                        <select class="form-control" id="destinations" name="destinations[]" multiple size="6">
                            @foreach ($destinations as $candidat)
                                <option value="{{ $candidat->id }}" @selected(collect(old('destinations', $destinationsSelectionnees))->contains(fn ($v) => (int) $v === $candidat->id))>
                                    {{ $candidat->nom }}{{ $candidat->section ? ' — '.$candidat->section->nom : '' }}{{ $candidat->option ? ' · '.$candidat->option->nom : '' }}{{ $candidat->cycle ? ' ('.$candidat->cycle->nom.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Pour une classe terminale de cycle (ex. fin de l'éducation de base) : cochez les classes
                            d'accueil (les 1res humanités). S'il y a plusieurs choix, la délibération demandera la
                            section/option de chaque élève admis. Vide = passage déduit du niveau.
                        </small>
                        @error('destinations')<div class="error">{{ $message }}</div>@enderror
                        @error('destinations.*')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label" for="statut">Statut *</label>
                        <select class="form-control" id="statut" name="statut" required>
                            <option value="actif" @selected(old('statut', $classe->statut ?? 'actif') === 'actif')>Active</option>
                            <option value="inactif" @selected(old('statut', $classe->statut ?? 'actif') === 'inactif')>Inactive</option>
                        </select>
                        @error('statut')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary" @disabled($cycles->isEmpty())><i class="fa fa-check"></i> {{ isset($classe) ? 'Enregistrer les modifications' : 'Enregistrer' }}</button>
                </div>
            </form>
        </div>
    </main>
    <script>
        const sectionSelect = document.getElementById('section_id');
        const optionSelect = document.getElementById('option_id');
        const createOptionLink = document.getElementById('create-option-link');
        const options = Array.from(optionSelect.options);

        function filterOptions() {
            const sectionId = sectionSelect.value;
            options.forEach((option, index) => {
                if (index === 0) return;
                const visible = Boolean(sectionId) && option.dataset.sectionId === sectionId;
                option.hidden = !visible;
                option.disabled = !visible;
                if (!visible && option.selected) option.selected = false;
            });
        }

        sectionSelect.addEventListener('change', filterOptions);
        sectionSelect.addEventListener('change', function () {
            const optionUrl = new URL(createOptionLink.href);
            if (sectionSelect.value) {
                optionUrl.searchParams.set('section_id', sectionSelect.value);
            } else {
                optionUrl.searchParams.delete('section_id');
            }
            createOptionLink.href = optionUrl.toString();
        });
        filterOptions();
    </script>
</body>
</html>