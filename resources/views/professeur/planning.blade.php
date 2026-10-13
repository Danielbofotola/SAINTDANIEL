@extends('professeur.layout')

@section('titre', 'Mon planning')
@section('titre-page')<i class="fa fa-calendar-o mr-2"></i> Mon planning @endsection
@section('sous-titre')
    {{ $schoolYear?->libelle ? 'Année scolaire '.$schoolYear->libelle : 'Année scolaire non définie' }}
    · {{ $emplois->count() }} créneau(x) par semaine · {{ $heuresParSemaine }} h
@endsection

@push('styles')
    @include('partials.horaire-grid-styles')
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-clock-o"></i></div><div class="stat-details"><h4>Heures / semaine</h4><p>{{ $heuresParSemaine }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-calendar-check-o"></i></div><div class="stat-details"><h4>Cours aujourd’hui</h4><p>{{ $aujourdhui->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-calendar"></i></div><div class="stat-details"><h4>Jours occupés</h4><p>{{ $parJour->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon dark"><i class="fa fa-university"></i></div><div class="stat-details"><h4>Classes</h4><p>{{ $affectations->pluck('classe_id')->unique()->count() }}</p></div></div></div>
    </div>

    @if ($aujourdhui->isNotEmpty())
        <div class="tile">
            <h3 class="tile-title">Aujourd’hui · {{ ucfirst($jourCourant ?? '') }}</h3>
            @foreach ($aujourdhui as $creneau)
                <div class="row-item">
                    <div class="row-time">{{ substr($creneau->heure_debut, 0, 5) }}<small>{{ substr($creneau->heure_fin, 0, 5) }}</small></div>
                    <div class="row-main"><div class="row-title">{{ $creneau->cours_nom }}</div><div class="row-meta">{{ $creneau->classe_nom }}</div></div>
                    <span class="pill">{{ $creneau->salle_nom ?: 'Salle à confirmer' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="tile">
        <h3 class="tile-title">Emploi du temps hebdomadaire <span class="pill ml-2">{{ $emplois->count() }} créneau(x)</span></h3>

        @include('partials.tableau-horaire', [
            'titre' => trim(($user->prenom ?? '').' '.($user->nom ?? '')) ?: 'Mon planning',
            'sousTitre' => $schoolYear?->libelle,
            'seances' => $seances,
            'afficherClasse' => true,
            'jours' => $jours,
            'aujourdhui' => $jourCourant,
            'pause' => ['debut' => '11:00', 'fin' => '11:30', 'label' => 'Pause Récréation'],
            'vide' => 'Aucun créneau à l’emploi du temps : votre planning s’affichera dès que l’administration aura saisi vos horaires.',
        ])
    </div>
@endsection
