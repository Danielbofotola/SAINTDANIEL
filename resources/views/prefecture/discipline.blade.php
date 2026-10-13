@extends('prefecture.layout')

@section('titre', 'Discipline & sanctions')

@section('actions')
    <form method="GET" class="form-inline">
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <select name="type" class="form-control mr-2">
            <option value="">Tous les types</option>
            @foreach ($types as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected($type === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
        <select name="statut" class="form-control mr-2">
            <option value="">Tous les statuts</option>
            @foreach ($statuts as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected($statut === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
@endsection

@section('contenu')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @unless ($annee)<div class="alert alert-warning">Aucune année scolaire en cours.</div>@endunless

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-gavel"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Sanctions</p><h4 class="card-title">{{ $stats['total'] }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-hourglass-half"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">En cours</p><h4 class="card-title">{{ $stats['en_cours'] }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-door-closed"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Exclusions</p><h4 class="card-title">{{ $stats['exclusions'] }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-users"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Élèves concernés</p><h4 class="card-title">{{ $stats['eleves'] }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row row-card-no-pd">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="fas fa-plus-circle"></i> Prononcer une sanction</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('prefecture.discipline.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Élève *</label>
                            <select name="eleve_id" class="form-control" required>
                                <option value="">Choisir un élève</option>
                                @foreach ($eleves as $eleve)
                                    <option value="{{ $eleve->id }}" @selected(old('eleve_id') == $eleve->id)>
                                        {{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }} — {{ $eleve->matricule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type *</label>
                            <select name="type" class="form-control" required>
                                @foreach ($types as $valeur => $libelle)
                                    <option value="{{ $valeur }}" @selected(old('type') === $valeur)>{{ $libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Motif *</label>
                            <textarea name="motif" class="form-control" rows="3" maxlength="2000" required placeholder="Faits reprochés…">{{ old('motif') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 form-group">
                                <label>Date *</label>
                                <input type="text" data-date-fr class="form-control" name="date_sanction" value="{{ old('date_sanction', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-6 form-group">
                                <label>Fin (exclusion)</label>
                                <input type="text" data-date-fr class="form-control" name="date_fin" value="{{ old('date_fin') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Observations</label>
                            <textarea name="observations" class="form-control" rows="2" maxlength="2000">{{ old('observations') }}</textarea>
                        </div>
                        <button class="btn btn-primary btn-round btn-block" type="submit" @disabled(! $annee)>
                            <i class="fas fa-save"></i> Enregistrer la sanction
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Registre des sanctions</h4>
                        <div class="card-category">{{ $schoolYear ?? $annee?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th><th>Classe</th><th>Type</th><th>Motif</th>
                                    <th>Date</th><th>Statut</th><th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sanctions as $sanction)
                                    <tr>
                                        <td class="fw-bold">
                                            <a href="{{ route('prefecture.eleves.fiche', $sanction->eleve_id) }}">
                                                {{ trim(($sanction->eleve?->nom ?? '').' '.($sanction->eleve?->postnom ?? '').' '.($sanction->eleve?->prenom ?? '')) }}
                                            </a>
                                            <br><small class="text-muted">{{ $sanction->prononcePar?->nom }}</small>
                                        </td>
                                        <td>{{ $sanction->classe?->nom ?? '—' }}</td>
                                        <td>{{ $sanction->libelleType() }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($sanction->motif, 80) }}</td>
                                        <td>
                                            {{ $sanction->date_sanction?->format('d/m/Y') }}
                                            @if ($sanction->date_fin)<br><small class="text-muted">→ {{ $sanction->date_fin->format('d/m/Y') }}</small>@endif
                                        </td>
                                        <td>
                                            @if ($sanction->statut === 'levee')
                                                <span class="badge badge-success">Levée</span>
                                            @else
                                                <span class="badge badge-warning">En cours</span>
                                            @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            @if ($sanction->statut !== 'levee')
                                                <form class="d-inline" method="POST" action="{{ route('prefecture.discipline.lever', $sanction) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-sm btn-round btn-outline-success" type="submit" title="Lever la sanction"><i class="fas fa-check"></i></button>
                                                </form>
                                            @endif
                                            <form class="d-inline" method="POST" action="{{ route('prefecture.discipline.destroy', $sanction) }}" onsubmit="return confirm('Annuler cette sanction ?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-round btn-outline-danger" type="submit" title="Annuler"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">Aucune sanction pour ce filtre.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
