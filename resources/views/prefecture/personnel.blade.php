@extends('prefecture.layout')

@section('titre', 'Personnel')

@section('actions')
    <span class="btn btn-light btn-round">
        <i class="far fa-calendar-alt"></i> {{ $schoolYear?->libelle ?? 'Année non définie' }}
    </span>
@endsection

@section('contenu')
    @php
        $professeursActifs = $professeurs->where('statut', 'actif')->count();
        $comptesActifs = $comptes->where('statut', 'actif')->count();
    @endphp

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-user-friends"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Comptes utilisateurs</p>
                                <h4 class="card-title">{{ $comptes->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-user-check"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Comptes actifs</p>
                                <h4 class="card-title">{{ $comptesActifs }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-chalkboard-teacher"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Professeurs enregistrés</p>
                                <h4 class="card-title">{{ $professeurs->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-user-tag"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Rôles représentés</p>
                                <h4 class="card-title">{{ $parRole->filter()->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><div class="card-title">Comptes par rôle</div></div>
                <div class="card-body">
                    @forelse ($parRole as $role => $total)
                        <div class="d-flex justify-content-between pb-1 pt-1">
                            <small class="fw-bold">{{ \App\Models\User::libelleRole($role) }}</small>
                            <span class="badge badge-count">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun compte.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Enseignants</div>
                        <div class="card-category">{{ $professeursActifs }} actif(s) / {{ $professeurs->count() }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 320px">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Matricule</th><th>Nom complet</th><th>Statut</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($professeurs as $professeur)
                                    <tr>
                                        <td>{{ $professeur->matricule }}</td>
                                        <td class="fw-bold">{{ trim($professeur->nom.' '.($professeur->postnom ?? '').' '.$professeur->prenom) }}</td>
                                        <td>
                                            <span class="badge {{ $professeur->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">
                                                {{ $professeur->statut }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Aucun enseignant enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Comptes utilisateurs</h4>
                        <div class="card-category">{{ $comptes->count() }} compte(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Identifiant</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th class="text-right">Dernière connexion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comptes as $compte)
                                    <tr>
                                        <td class="fw-bold">{{ trim(($compte->nom ?? '').' '.($compte->postnom ?? '').' '.($compte->prenom ?? '')) ?: $compte->username }}</td>
                                        <td>{{ $compte->username }}</td>
                                        <td>{{ \App\Models\User::libelleRole($compte->role) }}</td>
                                        <td>
                                            <span class="badge {{ $compte->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">
                                                {{ $compte->statut }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            {{ $compte->last_login?->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Aucun compte.</td></tr>
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
                        <h4 class="card-title">Documents des enseignants</h4>
                        <div class="card-category">{{ $documentsProfesseurs->count() }} fichier(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Enseignant</th><th>Titre</th><th>Type</th><th>Déposé le</th><th class="text-right">Fichier</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($documentsProfesseurs as $document)
                                    <tr>
                                        <td class="fw-bold">
                                            {{ $document->professeur ? trim($document->professeur->nom.' '.$document->professeur->prenom) : '—' }}
                                        </td>
                                        <td>{{ $document->titre }}</td>
                                        <td>{{ $document->type_document }}</td>
                                        <td>{{ $document->created_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ asset('storage/'.$document->chemin_fichier) }}" target="_blank" rel="noopener"
                                               class="btn btn-info btn-xs btn-round"><i class="fas fa-eye"></i> Ouvrir</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Aucun document enseignant.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
