<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Horaire &amp; Emploi du temps</title>
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
                <h1><i class="fa fa-clock-o"></i> Horaire &amp; Emploi du temps</h1>
                <p>Planifiez les créneaux par classe et par professeur @if($anneeEnCours) — année <strong>{{ $anneeEnCours->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.affectations.index') }}" class="btn btn-primary"><i class="fa fa-list"></i> Cours &amp; affectation</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouveau créneau</h4>
            @if ($affectations->isEmpty())
                <p class="text-muted mb-0">Aucune affectation active pour l'année en cours. Créez d'abord une affectation dans « Cours &amp; affectation ».</p>
            @else
                <form method="POST" action="{{ route('admin.emplois-du-temps.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-5 form-group"><label class="form-label">Affectation (classe — cours — professeur) *</label><select class="form-control" name="affectation_id" required><option value="">Choisir une affectation</option>@foreach ($affectations as $affectation)<option value="{{ $affectation->id }}" @selected(old('affectation_id') == $affectation->id)>{{ $affectation->classe?->nom }} — {{ $affectation->cours?->intitule }} — {{ $affectation->professeur?->nom }} {{ $affectation->professeur?->prenom }}</option>@endforeach</select></div>
                        <div class="col-md-2 form-group"><label class="form-label">Jour *</label><select class="form-control" name="jour" required>@foreach ($jours as $jour)<option value="{{ $jour }}" @selected(old('jour') === $jour)>{{ ucfirst($jour) }}</option>@endforeach</select></div>
                        <div class="col-md-2 form-group"><label class="form-label">Début *</label><input class="form-control" type="time" name="heure_debut" value="{{ old('heure_debut') }}" required></div>
                        <div class="col-md-2 form-group"><label class="form-label">Fin *</label><input class="form-control" type="time" name="heure_fin" value="{{ old('heure_fin') }}" required></div>
                        <div class="col-md-3 form-group"><label class="form-label">Salle</label><select class="form-control" name="salle_id"><option value="">Non attribuée</option>@foreach ($salles as $salle)<option value="{{ $salle->id }}" @selected(old('salle_id') == $salle->id)>{{ $salle->nom }}</option>@endforeach</select></div>
                        <div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Planifier</button></div>
                    </div>
                </form>
            @endif
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-calendar"></i> Créneaux planifiés</h4>
            <form class="row mb-3" method="GET" action="{{ route('admin.emplois-du-temps.index') }}">
                <div class="col-md-4 form-group mb-md-0"><select class="form-control" name="classe_id"><option value="">Toutes les classes</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                <div class="col-md-4 form-group mb-md-0"><select class="form-control" name="professeur_id"><option value="">Tous les professeurs</option>@foreach ($professeurs as $professeur)<option value="{{ $professeur->id }}" @selected((string) $professeurId === (string) $professeur->id)>{{ $professeur->nom }} {{ $professeur->prenom }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Jour</th><th>Horaire</th><th>Classe</th><th>Cours</th><th>Professeur</th><th>Salle</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($entrees as $entree)
                            <tr>
                                <td>{{ $entrees->firstItem() + $loop->index }}</td>
                                <td><span class="badge badge-info">{{ ucfirst($entree->jour) }}</span></td>
                                <td>{{ substr($entree->heure_debut, 0, 5) }} – {{ substr($entree->heure_fin, 0, 5) }}</td>
                                <td>{{ $entree->affectation?->classe?->nom }}</td>
                                <td>{{ $entree->affectation?->cours?->intitule }}</td>
                                <td>{{ $entree->affectation?->professeur?->nom }} {{ $entree->affectation?->professeur?->prenom }}</td>
                                <td>{{ $entree->salle?->nom ?? 'Non attribuée' }}</td>
                                <td>
                                    <form class="d-inline" method="POST" action="{{ route('admin.emplois-du-temps.destroy', $entree) }}" onsubmit="return confirm('Supprimer ce créneau ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Aucun créneau pour cette sélection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $entrees->total() }} créneau(x) — page {{ $entrees->currentPage() }} / {{ $entrees->lastPage() }}</small>
                {{ $entrees->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</body>
</html>
