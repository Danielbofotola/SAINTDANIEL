@extends('parent.layout')

@section('titre', 'Actualités')

@section('contenu')
    <div class="portail-card">
        <h2><i class="fa-solid fa-bullhorn text-warning"></i> Communications de l'établissement</h2>
        @forelse ($annonces as $annonce)
            <div class="py-2" style="border-bottom:1px solid var(--divider)">
                <span class="badge-niveau badge-{{ $annonce->niveau }}">{{ $annonce->libelleNiveau() }}</span>
                <strong class="ms-1" style="color:#fff">{{ $annonce->titre }}</strong>
                <div class="fs-7 text-muted-2 mt-1">{{ $annonce->message }}</div>
                <div class="fs-7 text-muted-2 mt-1">
                    <i class="fa-regular fa-clock"></i> {{ $annonce->created_at?->format('d/m/Y H:i') }}
                </div>
            </div>
        @empty
            <p class="text-muted-2 mb-0 fs-7">Aucune communication pour le moment.</p>
        @endforelse
    </div>
@endsection
