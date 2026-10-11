<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Élèves de {{ $classe->nom }}</title>
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
            <div>
                <h1><i class="fa fa-users"></i> Élèves de la classe {{ $classe->nom }}</h1>
                <p>{{ $classe->cycle?->nom ?? 'Cycle non renseigné' }} · {{ $classe->section?->nom ?? 'Section non renseignée' }} · {{ $classe->option?->nom ?? 'Option non renseignée' }}@if($anneeEnCours) · année <strong>{{ $anneeEnCours->libelle }}</strong>@endif</p>
            </div>
            <div><a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Retour aux classes</a> <a href="{{ route('admin.eleves.create') }}" class="btn btn-primary"><i class="fa fa-user-plus"></i> Inscrire un élève</a></div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Élèves actifs @if($anneeEnCours)<small>{{ $anneeEnCours->libelle }}</small>@endif</h4><p><b>{{ $inscriptions->count() }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-university fa-3x"></i><div class="info"><h4>Salle</h4><p><b>{{ $classe->salle?->nom ?? 'Non attribuée' }}</b></p></div></div></div>
        </div>

        <div class="tile">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Photo</th><th>Matricule</th><th>Nom complet</th><th>Sexe</th><th>Date de naissance</th><th>Date d'inscription</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($inscriptions as $inscription)
                            @php($eleve = $inscription->eleve)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-center">@if ($eleve?->photo)<img src="{{ asset('storage/'.$eleve->photo) }}" alt="Photo" width="40" height="40" class="rounded-circle" style="object-fit:cover">@else<i class="fa fa-user-circle fa-2x text-secondary"></i>@endif</td>
                                <td>{{ $eleve?->matricule ?? '—' }}</td>
                                <td>{{ $eleve?->nom }} {{ $eleve?->postnom }} {{ $eleve?->prenom }}</td>
                                <td>{{ $eleve?->sexe === 'M' ? 'Garçon' : 'Fille' }}</td>
                                <td>{{ $eleve?->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</td>
                                <td>{{ $inscription->date_inscription?->format('d/m/Y') ?? 'Non renseignée' }}</td>
                                <td><a href="{{ $eleve ? route('admin.eleves.show', $eleve) : '#' }}" class="btn btn-sm btn-info" title="Voir le dossier"><i class="fa fa-folder-open"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Aucun élève inscrit dans cette classe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>