@extends('professeur.layout')

@section('titre', 'Faire l’appel')
@section('titre-page')<i class="fa fa-check-square-o mr-2"></i> Faire l’appel @endsection
@section('sous-titre')
    @if ($affectation)
        {{ $affectation->cours_nom }} · {{ $affectation->classe_nom }} · séance du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
    @else
        Choisissez un cours pour enregistrer les présences.
    @endif
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if ($affectations->isEmpty())
        <div class="tile">
            <div class="empty"><i class="fa fa-check-square-o" aria-hidden="true"></i><strong>Aucun cours à appeler</strong>L’appel sera disponible dès que l’administration vous aura affecté des cours.</div>
        </div>
    @else
    <div class="tile">
        <h3 class="tile-title">Choisir la séance</h3>
        <form class="row" method="GET" action="{{ route('professeur.presences') }}">
            <div class="col-md-6 form-group">
                <label class="form-label">Cours &amp; classe</label>
                <select class="form-control" name="affectation" required>
                    @foreach ($affectations as $item)
                        <option value="{{ $item->id }}" @selected($affectation && $affectation->id === $item->id)>{{ $item->cours_nom }} — {{ $item->classe_nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label class="form-label">Date de la séance</label>
                <input class="form-control" type="text" data-date-fr name="date" value="{{ $date }}" required>
            </div>
            <div class="col-md-2 form-group d-flex align-items-end">
                <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-search"></i> Afficher</button>
            </div>
        </form>
    </div>

    @if ($affectation)
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-2 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-check"></i></div><div class="stat-details"><h4>Présents</h4><p>{{ $bilan['presents'] }}</p></div></div></div>
            <div class="col-sm-6 col-lg-2 mb-3"><div class="stat-card"><div class="stat-icon danger"><i class="fa fa-times"></i></div><div class="stat-details"><h4>Absents</h4><p>{{ $bilan['absents'] }}</p></div></div></div>
            <div class="col-sm-6 col-lg-2 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-clock-o"></i></div><div class="stat-details"><h4>Retards</h4><p>{{ $bilan['retards'] }}</p></div></div></div>
            <div class="col-sm-6 col-lg-2 mb-3"><div class="stat-card"><div class="stat-icon dark"><i class="fa fa-file-text-o"></i></div><div class="stat-details"><h4>Excusés</h4><p>{{ $bilan['excuses'] }}</p></div></div></div>
            <div class="col-sm-6 col-lg-2 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-users"></i></div><div class="stat-details"><h4>Appelés</h4><p>{{ $bilan['appele'] }}<small>sur {{ $bilan['total'] }}</small></p></div></div></div>
        </div>

        <div class="tile">
            <h3 class="tile-title">Feuille d’appel <span class="pill ml-2">{{ $eleves->count() }} élève(s)</span></h3>
            @if ($eleves->isEmpty())
                <div class="empty"><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève inscrit</strong>Les élèves apparaîtront dès les inscriptions de la classe.</div>
            @else
                <form method="POST" action="{{ route('professeur.presences.store') }}">
                    @csrf
                    <input type="hidden" name="affectation_id" value="{{ $affectation->id }}">
                    <input type="hidden" name="date_seance" value="{{ $date }}">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th style="width:110px">Matricule</th><th>Élève</th><th style="width:160px">Présence</th><th>Motif (si absent / retard)</th></tr></thead>
                            <tbody>
                                @foreach ($eleves as $eleve)
                                    @php($presence = $presences[$eleve->id] ?? null)
                                    <tr>
                                        <td>{{ $eleve->matricule }}</td>
                                        <td>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</td>
                                        <td>
                                            <select class="form-control form-control-sm" name="statuts[{{ $eleve->id }}]" required>
                                                @foreach ($statuts as $valeur => $libelle)
                                                    <option value="{{ $valeur }}" @selected(($presence->statut_presence ?? 'present') === $valeur)>{{ $libelle }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input class="form-control form-control-sm" type="text" maxlength="200" name="motifs[{{ $eleve->id }}]" value="{{ $presence->motif ?? '' }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer l’appel</button>
                        <span class="text-muted ml-2">L’appel déjà enregistré pour cette date est remplacé.</span>
                    </div>
                </form>
            @endif
        </div>

        @if ($historique->isNotEmpty())
            <div class="tile">
                <h3 class="tile-title">Historique des appels</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead><tr><th>Date</th><th class="text-right">Appelés</th><th class="text-right">Absents</th><th class="text-right">Retards</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($historique as $ligne)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($ligne->date_seance)->format('d/m/Y') }}</td>
                                    <td class="text-right">{{ $ligne->total }}</td>
                                    <td class="text-right">{{ $ligne->absents }}</td>
                                    <td class="text-right">{{ $ligne->retards }}</td>
                                    <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.presences', ['affectation' => $affectation->id, 'date' => $ligne->date_seance]) }}"><i class="fa fa-eye"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endif
@endsection
