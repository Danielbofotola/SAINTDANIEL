<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des professeurs</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-user-md"></i> Liste des professeurs</h1>
                <p>Corps enseignant de l'établissement @if($anneeEnCours) — année <strong>{{ $anneeEnCours->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.professeurs.create') }}" class="btn btn-primary"><i class="fa fa-user-plus"></i> Créer un professeur</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="widget-small primary coloured-icon"><i class="icon fa fa-user-md fa-3x"></i><div class="info"><h4>Professeurs</h4><p><b>{{ $stats['total'] }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small info coloured-icon"><i class="icon fa fa-check fa-3x"></i><div class="info"><h4>Actifs</h4><p><b>{{ $stats['actifs'] }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small warning coloured-icon"><i class="icon fa fa-user-times fa-3x"></i><div class="info"><h4>Inactifs</h4><p><b>{{ $stats['inactifs'] }}</b></p></div></div></div>
        </div>

        <div class="tile">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <form class="row mb-3" method="GET" action="{{ route('admin.professeurs.index') }}">
                <div class="col-md-6 form-group mb-md-0"><input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Rechercher par nom, matricule, spécialité ou e-mail…"></div>
                <div class="col-md-3 form-group mb-md-0"><select class="form-control" name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="actif" @selected($statut === 'actif')>Actif</option>
                    <option value="inactif" @selected($statut === 'inactif')>Inactif</option>
                </select></div>
                <div class="col-md-3"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Photo</th><th>Matricule</th><th>Nom complet</th><th>Spécialité</th><th>Téléphone</th><th>E-mail</th><th>Affectations @if($anneeEnCours)<small>{{ $anneeEnCours->libelle }}</small>@endif</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($professeurs as $professeur)
                            <tr>
                                <td>{{ $professeurs->firstItem() + $loop->index }}</td>
                                <td class="text-center">@if ($professeur->photo)<img src="{{ asset('storage/'.$professeur->photo) }}" alt="Photo" width="40" height="40" class="rounded-circle" style="object-fit:cover">@else<i class="fa fa-user-circle fa-2x text-secondary"></i>@endif</td>
                                <td>{{ $professeur->matricule }}</td>
                                <td><a href="{{ route('admin.professeurs.show', $professeur) }}"><strong>{{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }}</strong></a></td>
                                <td>{{ $professeur->specialite }}</td>
                                <td>{{ $professeur->telephone }}</td>
                                <td>{{ $professeur->email }}</td>
                                <td><span class="badge badge-info">{{ $professeur->affectations_count }} affectation(s)</span></td>
                                <td><span class="badge badge-{{ $professeur->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($professeur->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.professeurs.show', $professeur) }}" class="btn btn-sm btn-info" title="Voir le dossier"><i class="fa fa-folder-open"></i></a>
                                    <a href="{{ route('admin.professeurs.edit', $professeur) }}" class="btn btn-sm btn-warning" title="Modifier"><i class="fa fa-pencil"></i></a>
                                    <a href="{{ route('admin.professeurs.carte', $professeur) }}" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="Carte professionnelle / paiement"><i class="fa fa-id-card"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.professeurs.toggle-status', $professeur) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $professeur->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $professeur->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">Aucun professeur ne correspond à cette recherche.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $professeurs->total() }} professeur(s) — page {{ $professeurs->currentPage() }} / {{ $professeurs->lastPage() }}</small>
                {{ $professeurs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</body>
</html>
