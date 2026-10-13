<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Archives — anciens élèves</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
    @php($libelles = ['diplome' => 'Diplômé', 'exclu' => 'Renvoyé / exclu'])
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-box-archive"></i> Archives — anciens élèves</h1>
                <p>Élèves sortis de l'école : <strong>diplômés</strong> (fin d'études) et <strong>renvoyés</strong> (départ vers une autre école). Ils restent rangés et consultables.</p>
            </div>
            <div>
                <a href="{{ route('admin.eleves.index') }}" class="btn btn-secondary">Élèves actifs</a>
                <a href="{{ route('admin.eleves.desactives') }}" class="btn btn-secondary">Désactivés</a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Tableau de bord</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h4 class="tile-title mb-0"><i class="fa fa-list"></i> {{ $eleves->total() }} élève(s) archivé(s)</h4>
                <form class="form-inline" method="GET" action="{{ route('admin.eleves.archives') }}">
                    <input class="form-control mr-2" type="search" name="q" value="{{ $search }}" placeholder="Nom, prénom ou matricule…">
                    <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Rechercher</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Élève</th><th>Matricule</th><th>Sortie</th><th>Dernière classe</th><th>Année</th><th>Certificat / diplôme</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse ($eleves as $eleve)
                            @php($derniere = $eleve->inscriptions->first())
                            <tr>
                                <td>
                                    <a href="{{ route('admin.eleves.show', $eleve) }}">
                                        <strong>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</strong>
                                    </a>
                                </td>
                                <td>{{ $eleve->matricule }}</td>
                                <td>
                                    <span class="badge badge-{{ $eleve->statut === 'diplome' ? 'info' : 'warning' }}">
                                        {{ $libelles[$eleve->statut] ?? $eleve->statut }}
                                    </span>
                                </td>
                                <td>
                                    {{ $derniere?->classe?->nom ?? '—' }}
                                    @if ($derniere?->classe?->cycle)<span class="text-muted">· {{ $derniere->classe->cycle->nom }}</span>@endif
                                </td>
                                <td>{{ $derniere?->anneeScolaire?->libelle ?? '—' }}</td>
                                <td>
                                    @forelse ($eleve->certifications as $certification)
                                        <div>{{ $certification->libelle }} <small class="text-muted">({{ $certification->cycle?->nom ?? '—' }} · {{ $certification->date_obtention?->format('Y') }})</small></div>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td class="text-nowrap">
                                    <form class="d-inline" method="POST" action="{{ route('admin.eleves.toggle', $eleve) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-success" type="submit" title="Réintégrer comme élève actif"><i class="fa fa-undo"></i> Réintégrer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Aucun élève archivé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $eleves->total() }} élève(s) — page {{ $eleves->currentPage() }} / {{ $eleves->lastPage() }}</small>
                {{ $eleves->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</body>
</html>
