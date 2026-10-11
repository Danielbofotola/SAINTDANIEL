<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cours (branches)</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-book"></i> Cours (branches)</h1>
                <p>Créez, modifiez et activez les cours de l’établissement.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.affectations.index') }}" class="btn btn-primary"><i class="fa fa-th-list"></i> Affectations &amp; mutations</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouveau cours</h4>
            <form class="row" method="POST" action="{{ route('admin.cours.store') }}">
                @csrf
                <div class="col-md-2 form-group mb-md-0"><label class="form-label">Code *</label><input class="form-control" name="code" value="{{ old('code') }}" maxlength="20" required placeholder="MATH6"></div>
                <div class="col-md-4 form-group mb-md-0"><label class="form-label">Intitulé *</label><input class="form-control" name="intitule" value="{{ old('intitule') }}" maxlength="100" required placeholder="Mathématiques"></div>
                <div class="col-md-2 form-group mb-md-0"><label class="form-label">Pondération *</label><input class="form-control" type="number" name="ponderation" value="{{ old('ponderation', 1) }}" min="1" max="20" required></div>
                <div class="col-md-2 form-group mb-md-0"><label class="form-label">Max. Travaux journ.</label><input class="form-control" type="number" name="max_travaux_journaliers" value="{{ old('max_travaux_journaliers') }}" min="0" max="1000" placeholder="20"></div>
                <div class="col-md-2 form-group mb-md-0"><label class="form-label">Max. Examen</label><input class="form-control" type="number" name="max_examen" value="{{ old('max_examen') }}" min="0" max="1000" placeholder="40"></div>
                <div class="col-md-12 mt-2"><button class="btn btn-success" type="submit"><i class="fa fa-plus"></i> Ajouter le cours</button></div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Cours enregistrés <small class="text-muted">{{ $cours->count() }}</small></h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Code</th><th>Intitulé</th><th>Pondération</th><th>Max/période</th><th>Affectations</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($cours as $coursItem)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $coursItem->code }}</strong></td>
                                <td>{{ $coursItem->intitule }}</td>
                                <td>{{ $coursItem->ponderation }}</td>
                                <td>{{ $coursItem->maximumParPeriode() ?? '—' }}</td>
                                <td><span class="badge badge-info">{{ $coursItem->affectations_count }}</span></td>
                                <td><span class="badge badge-{{ $coursItem->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($coursItem->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-primary" title="Modifier" data-toggle="modal" data-target="#editCoursModal"
                                        data-id="{{ $coursItem->id }}"
                                        data-code="{{ $coursItem->code }}"
                                        data-intitule="{{ $coursItem->intitule }}"
                                        data-ponderation="{{ $coursItem->ponderation }}"
                                        data-tj="{{ $coursItem->max_travaux_journaliers }}"
                                        data-examen="{{ $coursItem->max_examen }}"
                                        data-action="{{ route('admin.cours.update', $coursItem) }}"><i class="fa fa-pencil"></i></button>
                                    <form class="d-inline" method="POST" action="{{ route('admin.cours.toggle-status', $coursItem) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $coursItem->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $coursItem->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.cours.destroy', $coursItem) }}" onsubmit="return confirm('Supprimer ce cours ?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Aucun cours enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="editCoursModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" method="POST" id="editCoursForm">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Modifier le cours</h5><button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Code *</label><input class="form-control" name="code" id="editCode" required maxlength="20"></div>
                    <div class="form-group"><label class="form-label">Intitulé *</label><input class="form-control" name="intitule" id="editIntitule" required maxlength="100"></div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label class="form-label">Pondération *</label><input class="form-control" type="number" name="ponderation" id="editPonderation" min="1" max="20" required></div>
                        <div class="col-md-4 form-group"><label class="form-label">Max. Travaux journ.</label><input class="form-control" type="number" name="max_travaux_journaliers" id="editTj" min="0" max="1000"></div>
                        <div class="col-md-4 form-group"><label class="form-label">Max. Examen</label><input class="form-control" type="number" name="max_examen" id="editExamen" min="0" max="1000"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer</button></div>
            </form>
        </div>
    </div>

    <script src="{{ asset('boostrap/css/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/bootstrap.min.js') }}"></script>
    <script>
        $('#editCoursModal').on('show.bs.modal', function (event) {
            var b = $(event.relatedTarget);
            var form = document.getElementById('editCoursForm');
            form.action = b.data('action');
            document.getElementById('editCode').value = b.data('code') || '';
            document.getElementById('editIntitule').value = b.data('intitule') || '';
            document.getElementById('editPonderation').value = b.data('ponderation') || 1;
            document.getElementById('editTj').value = b.data('tj') || '';
            document.getElementById('editExamen').value = b.data('examen') || '';
        });
    </script>
</body>
</html>
