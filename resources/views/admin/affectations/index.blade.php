<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cours &amp; affectation</title>
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
                <h1><i class="fa fa-list"></i> Cours &amp; affectation</h1>
                <p>Attribuez les cours aux classes et aux professeurs @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.emplois-du-temps.index') }}" class="btn btn-primary"><i class="fa fa-clock-o"></i> Emploi du temps</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours. Définissez-en une dans « Années scolaires » pour créer des affectations.</div>@endunless

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-plus"></i> Nouvelle affectation</h4>
                    <form method="POST" action="{{ route('admin.affectations.store') }}">
                        @csrf
                        <div class="form-group"><label class="form-label">Professeur *</label><select class="form-control" name="professeur_id" required><option value="">Choisir un professeur</option>@foreach ($professeurs as $professeur)<option value="{{ $professeur->id }}" @selected(old('professeur_id') == $professeur->id)>{{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }}</option>@endforeach</select></div>
                        <div class="form-group"><label class="form-label">Cours *</label><select class="form-control" name="cours_id" required><option value="">Choisir un cours</option>@foreach ($cours as $coursItem)<option value="{{ $coursItem->id }}" @selected(old('cours_id') == $coursItem->id)>{{ $coursItem->code }} — {{ $coursItem->intitule }}</option>@endforeach</select></div>
                        <div class="form-group"><label class="form-label">Classe *</label><select class="form-control" name="classe_id" required><option value="">Choisir une classe</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected(old('classe_id') == $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                        <button class="btn btn-primary" type="submit" @disabled(! $annee || $professeurs->isEmpty() || $cours->isEmpty() || $classes->isEmpty())><i class="fa fa-save"></i> Enregistrer l'affectation</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-exchange"></i> Mutation &amp; permutation</h4>
                    <p class="text-muted small mb-2">
                        La <strong>mutation</strong> réaffecte une affectation (professeur / cours / classe) depuis la liste ci-dessous.
                        La <strong>permutation</strong> échange les professeurs de deux affectations.
                    </p>
                    <form method="POST" action="{{ route('admin.affectations.permuter') }}">
                        @csrf
                        <div class="form-group"><label class="form-label">Affectation A *</label>
                            <select class="form-control" name="affectation_a" required>
                                <option value="">Choisir une affectation…</option>
                                @foreach ($toutesAffectations as $a)
                                    <option value="{{ $a->id }}">{{ $a->professeur?->nom }} {{ $a->professeur?->prenom }} — {{ $a->cours?->code }} {{ $a->classe?->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Affectation B *</label>
                            <select class="form-control" name="affectation_b" required>
                                <option value="">Choisir une affectation…</option>
                                @foreach ($toutesAffectations as $b)
                                    <option value="{{ $b->id }}">{{ $b->professeur?->nom }} {{ $b->professeur?->prenom }} — {{ $b->cours?->code }} {{ $b->classe?->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-warning" type="submit" @disabled(! $annee || $toutesAffectations->count() < 2)><i class="fa fa-exchange"></i> Permuter les professeurs</button>
                    </form>
                    <hr>
                    <a href="{{ route('admin.cours.index') }}" class="btn btn-outline-secondary btn-block"><i class="fa fa-book"></i> Gérer les cours (page dédiée)</a>
                </div>
            </div>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-th-list"></i> Affectations @if($annee)<small class="text-muted">{{ $annee->libelle }}</small>@endif</h4>
            <form class="row mb-3" method="GET" action="{{ route('admin.affectations.index') }}">
                <div class="col-md-7 form-group mb-md-0"><input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Rechercher par professeur, cours ou classe…"></div>
                <div class="col-md-3 form-group mb-md-0"><select class="form-control" name="classe_id"><option value="">Toutes les classes</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Professeur</th><th>Cours</th><th>Classe</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($affectations as $affectation)
                            <tr>
                                <td>{{ $affectations->firstItem() + $loop->index }}</td>
                                <td>{{ $affectation->professeur?->nom }} {{ $affectation->professeur?->prenom }}</td>
                                <td>{{ $affectation->cours?->code }} — {{ $affectation->cours?->intitule }}</td>
                                <td>{{ $affectation->classe?->nom }}</td>
                                <td><span class="badge badge-{{ $affectation->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($affectation->statut) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" title="Muter cette affectation"
                                        data-toggle="modal" data-target="#muterModal"
                                        data-action="{{ route('admin.affectations.muter', $affectation) }}"
                                        data-professeur="{{ $affectation->professeur_id }}"
                                        data-cours="{{ $affectation->cours_id }}"
                                        data-classe="{{ $affectation->classe_id }}"><i class="fa fa-exchange"></i></button>
                                    <form class="d-inline" method="POST" action="{{ route('admin.affectations.destroy', $affectation) }}" onsubmit="return confirm('Supprimer cette affectation ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune affectation pour cette sélection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $affectations->total() }} affectation(s) — page {{ $affectations->currentPage() }} / {{ $affectations->lastPage() }}</small>
                {{ $affectations->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>

    <div class="modal fade" id="muterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" method="POST" id="muterForm">
                @csrf @method('PATCH')
                <div class="modal-header"><h5 class="modal-title">Muter l’affectation</h5><button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Professeur *</label><select class="form-control" name="professeur_id" id="muterProfesseur" required><option value="">Choisir un professeur</option>@foreach ($professeurs as $professeur)<option value="{{ $professeur->id }}">{{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Cours *</label><select class="form-control" name="cours_id" id="muterCours" required><option value="">Choisir un cours</option>@foreach ($cours as $coursItem)<option value="{{ $coursItem->id }}">{{ $coursItem->code }} — {{ $coursItem->intitule }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Classe *</label><select class="form-control" name="classe_id" id="muterClasse" required><option value="">Choisir une classe</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}">{{ $classe->nom }}</option>@endforeach</select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button><button class="btn btn-warning" type="submit"><i class="fa fa-check"></i> Muter</button></div>
            </form>
        </div>
    </div>

    <script src="{{ asset('boostrap/css/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/bootstrap.min.js') }}"></script>
    <script>
        $('#muterModal').on('show.bs.modal', function (event) {
            var b = $(event.relatedTarget);
            document.getElementById('muterForm').action = b.data('action');
            document.getElementById('muterProfesseur').value = b.data('professeur') || '';
            document.getElementById('muterCours').value = b.data('cours') || '';
            document.getElementById('muterClasse').value = b.data('classe') || '';
        });
    </script>
</body>
</html>
