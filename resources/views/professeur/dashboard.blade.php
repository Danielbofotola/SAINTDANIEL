@extends('professeur.layout')

@php
    $evaluationMonths = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $evaluationTypes = ['interro' => 'Interrogation', 'devoir' => 'Devoir', 'examen' => 'Examen', 'autre' => 'Évaluation'];
    $todayLabel = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][now()->dayOfWeek];
@endphp

@section('titre', 'Vue générale')
@section('titre-page')<i class="fa fa-graduation-cap mr-2"></i> Bonjour, {{ $professor?->prenom ?: $user->prenom ?: $user->username }} @endsection
@section('sous-titre')
    {{ $schoolYear?->libelle ? 'Année scolaire '.$schoolYear->libelle : 'Année scolaire non définie' }}@if ($professor?->specialite) · {{ $professor->specialite }}@endif
    · {{ ucfirst($todayLabel) }} {{ now()->format('d/m/Y') }}
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ route('professeur.presences') }}"><i class="fa fa-check-square-o"></i> Faire l’appel</a>
    <a class="btn btn-outline-primary" href="{{ route('professeur.evaluations') }}"><i class="fa fa-pencil-square-o"></i> Évaluations</a>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-book"></i></div><div class="stat-details"><h4>Cours affectés</h4><p>{{ $assignments->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-university"></i></div><div class="stat-details"><h4>Classes suivies</h4><p>{{ $assignments->pluck('classe_id')->unique()->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-users"></i></div><div class="stat-details"><h4>Élèves concernés</h4><p>{{ number_format($studentCount, 0, ',', ' ') }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon danger"><i class="fa fa-pencil-square-o"></i></div><div class="stat-details"><h4>Évaluations à venir</h4><p>{{ $upcomingEvaluations->count() }}</p></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="tile" id="planning">
                <h3 class="tile-title">Mon planning · {{ ucfirst($todayLabel) }} <a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.planning') }}">Semaine complète</a></h3>
                @forelse ($todaySchedule as $item)
                    <div class="row-item">
                        <div class="row-time">{{ substr($item->heure_debut, 0, 5) }}<small>{{ substr($item->heure_fin, 0, 5) }}</small></div>
                        <div class="row-main"><div class="row-title">{{ $item->cours_nom }}</div><div class="row-meta">{{ $item->classe_nom }}</div></div>
                        <span class="pill">{{ $item->salle_nom ?: 'Salle à confirmer' }}</span>
                    </div>
                @empty
                    <div class="empty"><i class="fa fa-coffee" aria-hidden="true"></i><strong>Aucun cours prévu aujourd’hui</strong>{{ $nextClass ? 'Prochain cours : '.$nextClass->jour.' à '.substr($nextClass->heure_debut, 0, 5).'.' : 'Votre planning s’affichera dès que vos horaires seront disponibles.' }}</div>
                @endforelse
            </div>

            <div class="tile" id="enseignements">
                <h3 class="tile-title">Mes enseignements <span class="pill ml-2">{{ $assignments->count() }}</span> <a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.cours') }}">Tous mes cours</a></h3>
                @forelse ($assignments as $assignment)
                    <div class="row-item">
                        <div class="row-main">
                            <div class="row-title"><a href="{{ route('professeur.cours.show', $assignment->id) }}">{{ $assignment->cours_nom }}</a></div>
                            <div class="row-meta">{{ $assignment->classe_nom }}@if ($assignment->section_nom) · {{ $assignment->section_nom }}@endif @if ($assignment->option_nom) · {{ $assignment->option_nom }}@endif</div>
                        </div>
                        <span class="pill">{{ $assignment->cours_code }}</span>
                    </div>
                @empty
                    <div class="empty"><i class="fa fa-book" aria-hidden="true"></i><strong>Aucun cours affecté pour le moment</strong>Vos cours apparaîtront ici pour l’année scolaire en cours.</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-5">
            <div class="tile" id="evaluations">
                <h3 class="tile-title">Évaluations à venir <a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.evaluations') }}">Gérer</a></h3>
                @forelse ($upcomingEvaluations as $evaluation)
                    @php($d = \Carbon\Carbon::parse($evaluation->date_evaluation))
                    <div class="row-item">
                        <time class="date-chip" datetime="{{ $d->toDateString() }}"><strong>{{ $d->format('d') }}</strong><span>{{ $evaluationMonths[(int) $d->format('n') - 1] }}</span></time>
                        <div class="row-main"><div class="row-title">{{ $evaluation->titre }}</div><div class="row-meta">{{ $evaluationTypes[$evaluation->type_evaluation] ?? 'Évaluation' }} · {{ $evaluation->classe_nom }}</div></div>
                    </div>
                @empty
                    <div class="empty"><i class="fa fa-check" aria-hidden="true"></i><strong>Rien de planifié pour l’instant</strong>Les prochaines évaluations de vos classes apparaîtront ici.</div>
                @endforelse
            </div>

            <div class="tile">
                <h3 class="tile-title">Accès rapides</h3>
                <div class="row-item">
                    <div class="row-main"><div class="row-title"><a href="{{ route('professeur.presences') }}">Faire l’appel</a></div><div class="row-meta">Présences, absences et retards par séance</div></div>
                    <span class="pill"><i class="fa fa-check-square-o"></i></span>
                </div>
                <div class="row-item">
                    <div class="row-main"><div class="row-title"><a href="{{ route('professeur.eleves') }}">Mes élèves</a></div><div class="row-meta">Élèves de vos classes et leurs résultats</div></div>
                    <span class="pill"><i class="fa fa-users"></i></span>
                </div>
                <div class="row-item">
                    <div class="row-main"><div class="row-title"><a href="{{ route('professeur.planning') }}">Mon planning</a></div><div class="row-meta">Emploi du temps de la semaine</div></div>
                    <span class="pill"><i class="fa fa-calendar-o"></i></span>
                </div>
            </div>
        </div>
    </div>
@endsection
