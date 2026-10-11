@extends('prefecture.layout')

@section('titre', 'Dossiers élèves')

@section('actions')
    <form method="GET" class="form-inline">
        <input type="text" name="q" value="{{ $recherche }}" class="form-control mr-2"
               placeholder="Nom, post-nom, prénom ou matricule">
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-search"></i> Rechercher</button>
    </form>
@endsection

@section('contenu')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Élèves de l'établissement</h4>
                        <div class="card-category">
                            {{ $eleves->total() }} élève(s) @if ($recherche !== '') · recherche « {{ $recherche }} » @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Tri/recherche DataTables sur la page ; la pagination reste serveur. --}}
                        <table class="table table-hover" data-datatable-paging="off">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matricule</th>
                                    <th>Classe (année en cours)</th>
                                    <th>Tuteur</th>
                                    <th>Contact tuteur</th>
                                    <th class="text-right">Documents</th>
                                    <th class="text-right">Dossier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($eleves as $eleve)
                                    @php($inscription = $eleve->inscriptions->first())
                                    <tr>
                                        <td class="fw-bold">{{ trim($eleve->nom.' '.($eleve->postnom ?? '').' '.$eleve->prenom) }}</td>
                                        <td>{{ $eleve->matricule ?: '—' }}</td>
                                        <td>{{ $inscription?->classe?->nom ?? '—' }}</td>
                                        <td>{{ $eleve->tuteur ? trim(($eleve->tuteur->nom_pere ?: $eleve->tuteur->nom_mere ?: '—')) : '—' }}</td>
                                        <td>{{ $eleve->tuteur?->telephone_pere ?: ($eleve->tuteur?->telephone_mere ?: '—') }}</td>
                                        <td class="text-right">
                                            @if ($eleve->documents_count > 0)
                                                <span class="badge badge-count">{{ $eleve->documents_count }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('prefecture.eleves.fiche', $eleve) }}" class="btn btn-info btn-xs btn-round">
                                                <i class="fas fa-folder-open"></i> Ouvrir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Aucun élève ne correspond à cette recherche.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($eleves->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                {{ $eleves->firstItem() }}–{{ $eleves->lastItem() }} sur {{ $eleves->total() }}
                            </small>
                            <span>
                                @unless ($eleves->onFirstPage())
                                    <a href="{{ $eleves->previousPageUrl() }}" class="btn btn-light btn-round btn-sm">
                                        <i class="fas fa-angle-left"></i> Précédent
                                    </a>
                                @endunless
                                @if ($eleves->hasMorePages())
                                    <a href="{{ $eleves->nextPageUrl() }}" class="btn btn-light btn-round btn-sm">
                                        Suivant <i class="fas fa-angle-right"></i>
                                    </a>
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
