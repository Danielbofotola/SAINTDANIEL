@extends('professeur.layout')

@section('titre', 'Évaluations & notes')
@section('titre-page')<i class="fa fa-pencil-square-o mr-2"></i> Évaluations &amp; notes @endsection
@section('sous-titre', $evaluations->count().' évaluation(s) · '.$aVenir->count().' à venir')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="tile">
                <h3 class="tile-title">Nouvelle évaluation</h3>
                <form method="POST" action="{{ route('professeur.evaluations.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Cours &amp; classe *</label>
                        <select class="form-control" name="affectation_id" required>
                            @foreach ($affectations as $affectation)
                                <option value="{{ $affectation->id }}" @selected(old('affectation_id') == $affectation->id)>{{ $affectation->cours_nom }} — {{ $affectation->classe_nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Titre *</label>
                        <input class="form-control" name="titre" value="{{ old('titre') }}" maxlength="100" required placeholder="Ex. Interro n°1">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Type *</label>
                            <select class="form-control" name="type_evaluation" required>
                                @foreach ($types as $valeur => $libelle)
                                    <option value="{{ $valeur }}" @selected(old('type_evaluation') === $valeur)>{{ $libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Note max. <small class="text-muted">(auto)</small></label>
                            <input class="form-control" type="number" step="0.5" min="1" max="1000" name="max_note" value="{{ old('max_note') }}" placeholder="Barème du cours">
                            <small class="text-muted">Vide = barème du cours.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Période *</label>
                        <select class="form-control" name="periode_id" required>
                            @forelse ($periodes as $periode)
                                <option value="{{ $periode->id }}" @selected(old('periode_id') == $periode->id)>{{ $periode->nom }}</option>
                            @empty
                                <option value="">Aucune période définie</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date *</label>
                        <input class="form-control" type="text" data-date-fr name="date_evaluation" value="{{ old('date_evaluation', now()->toDateString()) }}" required>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit" @disabled($affectations->isEmpty() || $periodes->isEmpty())><i class="fa fa-plus"></i> Créer et saisir les notes</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tile">
                <h3 class="tile-title">Évaluations à venir <span class="pill ml-2">{{ $aVenir->count() }}</span></h3>
                @forelse ($aVenir as $evaluation)
                    @php($d = \Carbon\Carbon::parse($evaluation->date_evaluation))
                    <div class="row-item">
                        <time class="date-chip" datetime="{{ $d->toDateString() }}"><strong>{{ $d->format('d') }}</strong><span>{{ ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'][(int) $d->format('n') - 1] }}</span></time>
                        <div class="row-main">
                            <div class="row-title">{{ $evaluation->titre }}</div>
                            <div class="row-meta">{{ $types[$evaluation->type_evaluation] ?? 'Évaluation' }} · {{ $evaluation->cours_nom }} · {{ $evaluation->classe_nom }}@if ($evaluation->periode_nom) · {{ $evaluation->periode_nom }}@endif</div>
                        </div>
                        <span class="pill">{{ $evaluation->notes_count }} / {{ $effectifs[$evaluation->classe_id] ?? 0 }} notes</span>
                        <a class="btn btn-sm btn-primary ml-2" href="{{ route('professeur.notes', $evaluation->id) }}"><i class="fa fa-pencil"></i> Notes</a>
                    </div>
                @empty
                    <div class="empty"><i class="fa fa-check" aria-hidden="true"></i><strong>Rien de planifié</strong>Créez une évaluation pour saisir les notes de vos classes.</div>
                @endforelse
            </div>

            <div class="tile">
                <h3 class="tile-title">Évaluations passées <span class="pill ml-2">{{ $passees->count() }}</span></h3>
                @forelse ($passees as $evaluation)
                    @php($d = \Carbon\Carbon::parse($evaluation->date_evaluation))
                    <div class="row-item">
                        <time class="date-chip" datetime="{{ $d->toDateString() }}"><strong>{{ $d->format('d') }}</strong><span>{{ ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'][(int) $d->format('n') - 1] }}</span></time>
                        <div class="row-main">
                            <div class="row-title">{{ $evaluation->titre }}</div>
                            <div class="row-meta">{{ $types[$evaluation->type_evaluation] ?? 'Évaluation' }} · {{ $evaluation->cours_nom }} · {{ $evaluation->classe_nom }}</div>
                        </div>
                        <span class="pill">{{ $evaluation->notes_count }} note(s)</span>
                        <a class="btn btn-sm btn-outline-primary ml-2" href="{{ route('professeur.notes', $evaluation->id) }}"><i class="fa fa-eye"></i> Notes</a>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune évaluation passée.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
