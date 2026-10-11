@extends('prefecture.layout')

@section('titre', 'Résultats & bulletins')

@section('actions')
    <form method="GET" class="form-inline">
        <select name="periode" class="form-control mr-2">
            <option value="">Toutes les périodes</option>
            @foreach ($periodes as $periode)
                <option value="{{ $periode->id }}" @selected((string) $periodeId === (string) $periode->id)>{{ $periode->nom }}</option>
            @endforeach
        </select>
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
@endsection

@section('contenu')
    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-file-alt"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Bulletins</p><h4 class="card-title">{{ $bulletins->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-double"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Approuvés</p><h4 class="card-title">{{ $approuves }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-file-pdf"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Avec document PDF</p><h4 class="card-title">{{ $avecPdf }}</h4>
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
                        <h4 class="card-title">Synthèses par élève et par période</h4>
                        <div class="card-category">{{ $schoolYear?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Classe</th>
                                    <th>Période</th>
                                    <th class="text-right">Points</th>
                                    <th class="text-right">%</th>
                                    <th class="text-right">Place</th>
                                    <th>Mention</th>
                                    <th class="text-right">Document</th>
                                    <th class="text-right">Approbation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bulletins as $bulletin)
                                    @php($eleve = $bulletin->inscription?->eleve)
                                    <tr>
                                        <td class="fw-bold">
                                            @if ($eleve)
                                                <a href="{{ route('prefecture.eleves.fiche', $eleve) }}">
                                                    {{ trim($eleve->nom.' '.($eleve->postnom ?? '').' '.$eleve->prenom) }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $bulletin->inscription?->classe?->nom ?? '—' }}</td>
                                        <td>{{ $bulletin->periode?->nom ?? '—' }}</td>
                                        <td class="text-right">{{ (float) $bulletin->total_points }} / {{ (float) $bulletin->max_possible }}</td>
                                        <td class="text-right fw-bold">{{ (float) $bulletin->pourcentage }}%</td>
                                        <td class="text-right">{{ $bulletin->place_classe ?? '—' }}</td>
                                        <td>{{ $bulletin->mention ?: '—' }}</td>
                                        <td class="text-right">
                                            {{-- Le bulletin est généré à la volée : toujours consultable et imprimable. --}}
                                            <a href="{{ route('prefecture.bulletins.document', $bulletin->inscription_id) }}"
                                               class="btn btn-info btn-xs btn-round" target="_blank" rel="noopener">
                                                <i class="fas fa-file-alt"></i> Afficher
                                            </a>
                                        </td>
                                        <td class="text-right">
                                            @if ($bulletin->approuve_at)
                                                <span class="badge badge-success">
                                                    Approuvé le {{ $bulletin->approuve_at->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('prefecture.bulletins.approuver', $bulletin) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-round btn-outline-primary">
                                                        <i class="fas fa-stamp"></i> Approuver
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">
                                        Aucun bulletin généré pour ce filtre. L'administration les génère depuis le module Bulletins.
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
