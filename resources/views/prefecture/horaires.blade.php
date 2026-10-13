@extends('prefecture.layout')

@section('titre', 'Horaire des cours')

@push('styles')
    @include('partials.horaire-grid-styles')
@endpush

@section('actions')
    <form method="GET" class="form-inline">
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <select name="professeur" class="form-control mr-2">
            <option value="">Tous les enseignants</option>
            @foreach ($professeurs as $professeur)
                <option value="{{ $professeur->id }}" @selected((string) $professeurId === (string) $professeur->id)>
                    {{ trim($professeur->nom.' '.$professeur->prenom) }}
                </option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
    <a href="{{ route('admin.emplois-du-temps.index') }}" class="btn btn-light btn-round">
        <i class="fas fa-pen"></i> Modifier l'horaire
    </a>
@endsection

@section('contenu')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info bg-info-gradient">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0 fw-bold">{{ $total }} créneau(x) de cours</h4>
                    <small>{{ $schoolYear?->libelle }}</small>
                </div>
            </div>
        </div>
    </div>

    @php
        $pause = ['debut' => '11:00', 'fin' => '11:30', 'label' => 'Pause Récréation'];
    @endphp

    {{-- Vue globale de l'horaire de chaque classe --}}
    <div class="row">
        <div class="col-md-12">
            <h5 class="section-titre mt-2 mb-3"><i class="fas fa-table mr-2"></i> Horaire par classe</h5>
        </div>
    </div>

    @forelse ($parClasse as $grille)
        @include('partials.tableau-horaire', [
            'titre' => $grille['titre'],
            'sousTitre' => 'Emploi du temps hebdomadaire',
            'seances' => $grille['seances'],
            'afficherProfesseur' => true,
            'jours' => $jours,
            'aujourdhui' => $aujourdhui,
            'pause' => $pause,
            'vide' => 'Aucun créneau planifié pour cette classe.',
        ])
    @empty
        <div class="row">
            <div class="col-md-12">
                <div class="card"><div class="card-body text-center text-muted">
                    Aucune classe ne correspond à ce filtre.
                </div></div>
            </div>
        </div>
    @endforelse

    {{-- Vue globale de l'horaire de chaque enseignant --}}
    <div class="row">
        <div class="col-md-12">
            <h5 class="section-titre mt-4 mb-3"><i class="fas fa-user-clock mr-2"></i> Horaire par enseignant</h5>
        </div>
    </div>

    @forelse ($parProfesseur as $grille)
        @include('partials.tableau-horaire', [
            'titre' => $grille['titre'],
            'sousTitre' => 'Emploi du temps hebdomadaire',
            'seances' => $grille['seances'],
            'afficherClasse' => true,
            'jours' => $jours,
            'aujourdhui' => $aujourdhui,
            'pause' => $pause,
            'vide' => 'Aucun créneau planifié pour cet enseignant.',
        ])
    @empty
        <div class="row">
            <div class="col-md-12">
                <div class="card"><div class="card-body text-center text-muted">
                    Aucun enseignant ne correspond à ce filtre.
                </div></div>
            </div>
        </div>
    @endforelse
@endsection
