@extends('prefecture.layout')

@section('titre', 'Tuteurs &amp; parents')

@section('contenu')
    @php
        $tuteurs = $parTuteur->map(fn ($eleves) => ['tuteur' => $eleves->first()->tuteur, 'eleves' => $eleves]);
    @endphp

    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-user-friends"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Fiches tuteurs</p><h4 class="card-title">{{ $tuteurs->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center {{ $sansTuteur > 0 ? 'icon-danger' : 'icon-success' }} bubble-shadow-small"><i class="fas fa-user-slash"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Élèves sans tuteur</p><h4 class="card-title">{{ $sansTuteur }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-mobile-alt"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Comptes parents</p><h4 class="card-title">{{ $comptesParents->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Contacts des tuteurs</h4>
                        <div class="card-category">Réception des parents · {{ $schoolYear?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Père</th>
                                    <th>Mère</th>
                                    <th>Téléphones</th>
                                    <th>Enfants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tuteurs as $entree)
                                    <tr>
                                        <td class="fw-bold">
                                            {{ $entree['tuteur']->nom_pere ?: '—' }}
                                            @if ($entree['tuteur']->profession_pere)
                                                <small class="text-muted d-block">{{ $entree['tuteur']->profession_pere }}</small>
                                            @endif
                                        </td>
                                        <td class="fw-bold">
                                            {{ $entree['tuteur']->nom_mere ?: '—' }}
                                            @if ($entree['tuteur']->profession_mere)
                                                <small class="text-muted d-block">{{ $entree['tuteur']->profession_mere }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $entree['tuteur']->telephone_pere ?: '—' }}
                                            @if ($entree['tuteur']->telephone_mere)
                                                <small class="text-muted d-block">{{ $entree['tuteur']->telephone_mere }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($entree['eleves'] as $enfant)
                                                @php($inscription = $enfant->inscriptions->first())
                                                <div class="py-1">
                                                    <a href="{{ route('prefecture.eleves.fiche', $enfant) }}">
                                                        {{ trim($enfant->nom.' '.($enfant->postnom ?? '').' '.$enfant->prenom) }}
                                                    </a>
                                                    @if ($inscription?->classe)
                                                        <span class="badge badge-count">{{ $inscription->classe->nom }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Aucun tuteur enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Comptes parents et enfants rattachés</h4>
                        <div class="card-category">Rattachement défini par l'administration</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Parent</th><th>Identifiant</th><th>Téléphone</th><th>Enfants suivis</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($comptesParents as $compte)
                                    <tr>
                                        <td class="fw-bold">{{ trim(($compte->nom ?? '').' '.($compte->prenom ?? '')) ?: $compte->username }}</td>
                                        <td>{{ $compte->username }}</td>
                                        <td>{{ $compte->telephone ?: '—' }}</td>
                                        <td>
                                            @forelse ($compte->eleves as $enfant)
                                                <div class="py-1">
                                                    <a href="{{ route('prefecture.eleves.fiche', $enfant) }}">
                                                        {{ trim($enfant->nom.' '.($enfant->prenom ?? '')) }}
                                                    </a>
                                                </div>
                                            @empty
                                                <small class="text-muted">Aucun enfant rattaché</small>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Aucun compte parent.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
