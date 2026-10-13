<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscriptions en attente</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-hourglass-half"></i> Inscriptions en attente</h1>
                <p>
                    Inscriptions non validées par la caisse pour
                    {{ $annee ? $annee->libelle : "l'année en cours" }}.
                    Supprimer l'inscription ne supprime pas l'élève : sa fiche et son tuteur restent enregistrés.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.eleves.index') }}" class="btn btn-secondary">Retour aux élèves</a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Tableau de bord</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="tile-title mb-0"><i class="fa fa-list"></i> {{ $inscriptions->count() }} inscription(s) en attente</h4>
                @if ($inscriptions->isNotEmpty())
                    <form method="POST" action="{{ route('admin.inscriptions.attente.purge') }}" onsubmit="return confirm('Supprimer TOUTES les inscriptions en attente de l’année ? Les élèves seront conservés.');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger" type="submit"><i class="fa fa-broom"></i> Tout supprimer</button>
                    </form>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th><th>Élève</th><th>Matricule</th><th>Classe visée</th><th>Date d'inscription</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscriptions as $inscription)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $inscription->eleve?->nom }} {{ $inscription->eleve?->postnom }} {{ $inscription->eleve?->prenom }}</strong>
                                </td>
                                <td>{{ $inscription->eleve?->matricule ?? '—' }}</td>
                                <td>
                                    {{ $inscription->classe?->nom ?? '—' }}
                                    @if ($inscription->classe?->cycle)
                                        <span class="text-muted">· {{ $inscription->classe->cycle->nom }}</span>
                                    @endif
                                    @if ($inscription->classe?->section)
                                        <span class="text-muted">· {{ $inscription->classe->section->nom }}</span>
                                    @endif
                                </td>
                                <td>{{ $inscription->date_inscription?->format('d/m/Y') }}</td>
                                <td class="text-nowrap">
                                    <form class="d-inline" method="POST" action="{{ route('admin.inscriptions.attente.destroy', $inscription) }}" onsubmit="return confirm('Supprimer cette inscription en attente ? L’élève sera conservé.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer l'inscription"><i class="fa fa-trash"></i> Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune inscription en attente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
