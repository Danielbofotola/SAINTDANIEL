@extends('professeur.layout')

@section('titre', 'Mes cours')
@section('titre-page')<i class="fa fa-book mr-2"></i> Mes cours @endsection
@section('sous-titre', $affectations->count().' cours affecté(s) · '.$elevesCount.' élève(s) concerné(s)')

@section('content')
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-book"></i></div><div class="stat-details"><h4>Cours affectés</h4><p>{{ $affectations->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-university"></i></div><div class="stat-details"><h4>Classes suivies</h4><p>{{ $affectations->pluck('classe_id')->unique()->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-users"></i></div><div class="stat-details"><h4>Élèves concernés</h4><p>{{ number_format($elevesCount, 0, ',', ' ') }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon dark"><i class="fa fa-clock-o"></i></div><div class="stat-details"><h4>Heures / semaine</h4><p>{{ $heuresParSemaine }}</p></div></div></div>
    </div>

    <div class="tile">
        <h3 class="tile-title">Mes enseignements</h3>
        @forelse ($affectations as $affectation)
            <div class="row-item">
                <div class="row-main">
                    <div class="row-title">
                        <a href="{{ route('professeur.cours.show', $affectation->id) }}">{{ $affectation->cours_nom }}</a>
                    </div>
                    <div class="row-meta">
                        {{ $affectation->classe_nom }}
                        @if ($affectation->section_nom) · {{ $affectation->section_nom }}@endif
                        @if ($affectation->option_nom) · {{ $affectation->option_nom }}@endif
                        · {{ $effectifs[$affectation->classe_id] ?? 0 }} élève(s)
                        · {{ $creneauxParAffectation[$affectation->id] ?? 0 }} créneau(x)
                        · {{ $evaluationsParAffectation[$affectation->id] ?? 0 }} évaluation(s)
                    </div>
                </div>
                <span class="pill">{{ $affectation->cours_code }}</span>
                <a class="btn btn-sm btn-outline-primary ml-2" href="{{ route('professeur.cours.show', $affectation->id) }}">Ouvrir</a>
            </div>
        @empty
            <div class="empty"><i class="fa fa-book" aria-hidden="true"></i><strong>Aucun cours affecté pour le moment</strong>Vos cours apparaîtront ici pour l’année scolaire en cours.</div>
        @endforelse
    </div>
@endsection
