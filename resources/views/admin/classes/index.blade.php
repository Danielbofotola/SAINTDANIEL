<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des classes</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div><h1><i class="fa fa-graduation-cap"></i> Classes créées</h1><p>Consultez les classes et leur organisation scolaire @if($anneeEnCours) — année <strong>{{ $anneeEnCours->libelle }}</strong>@endif.</p></div>
            <div><a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a> <a href="{{ route('admin.classes.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Créer une classe</a></div>
        </div>

        <div class="tile">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                    <thead><tr><th>#</th><th>Classe</th><th>Niveau</th><th>Si admis →</th><th>Cycle</th><th>Section</th><th>Option</th><th>Salle</th><th>Élèves @if($anneeEnCours)<small>{{ $anneeEnCours->libelle }}</small>@endif</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($classes as $classe)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('admin.classes.students', $classe) }}"><strong>{{ $classe->nom }}</strong></a>@if ($classe->section_id)<br><small class="text-muted">Section {{ $classe->section?->nom }}</small>@endif</td>
                                <td>{{ $classe->ordre ?? '—' }}</td>
                                @php($suivante = $classe->ordre === null ? null : $classe->suivante())
                                <td>
                                    @if ($classe->ordre === null)
                                        <span class="badge badge-warning" title="Renseignez le niveau de cette classe">Niveau à définir</span>
                                    @elseif ($suivante)
                                        <span class="badge badge-success" title="Classe des admis pour l'année suivante">{{ $suivante->nom }}</span>
                                    @else
                                        <span class="badge badge-secondary" title="Aucune classe de niveau supérieur dans ce cycle : les admis n'ont pas de cible">Fin de cycle</span>
                                    @endif
                                </td>
                                <td>{{ $classe->cycle?->nom ?? 'Non renseigné' }}</td>
                                <td>{{ $classe->section?->nom ?? 'Non renseignée' }}</td>
                                <td>{{ $classe->option?->nom ?? 'Non renseignée' }}</td>
                                <td>{{ $classe->salle?->nom ?? 'Non attribuée' }}</td>
                                <td><a href="{{ route('admin.classes.students', $classe) }}" class="badge badge-info">{{ $classe->eleves_count }} élève(s)</a></td>
                                <td><span class="badge badge-{{ $classe->statut === 'actif' ? 'success' : 'secondary' }}">{{ $classe->statut === 'actif' ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.classes.edit', $classe) }}" class="btn btn-sm btn-info" title="Modifier"><i class="fa fa-pencil"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.classes.toggle-status', $classe) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $classe->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $classe->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.classes.destroy', $classe) }}" onsubmit="return confirm('Voulez-vous vraiment supprimer cette classe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Aucune classe créée pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>