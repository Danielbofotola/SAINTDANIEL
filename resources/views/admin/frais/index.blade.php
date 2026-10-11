<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Types de frais &amp; tarifs</title>
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
                <h1><i class="fa fa-money"></i> Types de frais &amp; tarifs</h1>
                <p>Définissez les frais scolaires et leurs montants par classe @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.paiements.index') }}" class="btn btn-primary"><i class="fa fa-credit-card"></i> Paiements</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours : les tarifs ne peuvent pas être enregistrés.</div>@endunless

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-plus"></i> Nouveau type de frais</h4>
                    <form method="POST" action="{{ route('admin.frais.types.store') }}">
                        @csrf
                        <div class="form-group"><label class="form-label">Nom *</label><input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="100" required placeholder="Ex. Frais scolaires, Tenue, Cantine…"></div>
                        <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2" maxlength="1000">{{ old('description') }}</textarea></div>
                        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="est_inscription" id="est_inscription" value="1" @checked(old('est_inscription'))><label class="form-check-label" for="est_inscription">Frais d’inscription — son paiement valide automatiquement l’inscription de l’élève</label></div>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Créer le type de frais</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-tags"></i> Nouveau tarif (par classe)</h4>
                    @if ($typesActifs->isEmpty() || $classes->isEmpty())
                        <p class="text-muted mb-0">Créez au moins un type de frais et une classe avant d'enregistrer un tarif.</p>
                    @else
                        <form method="POST" action="{{ route('admin.frais.tarifs.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group"><label class="form-label">Type de frais *</label><select class="form-control" name="type_frais_id" required><option value="">Choisir</option>@foreach ($typesActifs as $type)<option value="{{ $type->id }}" @selected(old('type_frais_id') == $type->id)>{{ $type->nom }}</option>@endforeach</select></div>
                                <div class="col-md-6 form-group"><label class="form-label">Classe *</label><select class="form-control" name="classe_id" required><option value="">Choisir</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected(old('classe_id') == $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                                <div class="col-md-4 form-group"><label class="form-label">Montant exigé *</label><input class="form-control" type="number" step="0.01" min="0" name="montant_exige" value="{{ old('montant_exige') }}" required></div>
                                <div class="col-md-2 form-group"><label class="form-label">Devise *</label><select class="form-control" name="devise">@foreach (\App\Support\Monnaie::DEVISES as $code => $libelle)<option value="{{ $code }}" @selected(old('devise', 'USD') === $code)>{{ $code }}</option>@endforeach</select></div>
                                <div class="col-md-6 form-group"><label class="form-label">Date d'échéance *</label><input class="form-control" type="text" data-date-fr name="date_echeance" value="{{ old('date_echeance', now()->toDateString()) }}" required></div>
                            </div>
                            <button class="btn btn-success" type="submit" @disabled(! $annee)><i class="fa fa-plus"></i> Enregistrer le tarif</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-list"></i> Types de frais</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Nom</th><th>Description</th><th>Tarifs</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($types as $type)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $type->nom }}</strong></td>
                                <td>{{ $type->description ?: '—' }}</td>
                                <td><span class="badge badge-info">{{ $type->frais_scolaires_count }}</span></td>
                                <td><span class="badge badge-{{ $type->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($type->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <form class="d-inline" method="POST" action="{{ route('admin.frais.types.toggle-status', $type) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $type->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $type->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.frais.types.destroy', $type) }}" onsubmit="return confirm('Supprimer ce type de frais ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun type de frais enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-tags"></i> Tarifs @if($annee)<small class="text-muted">{{ $annee->libelle }}</small>@endif</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Type de frais</th><th>Classe</th><th>Montant exigé</th><th>Devise</th><th>Échéance</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($tarifs as $tarif)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tarif->typeFrais?->nom ?? '—' }}</td>
                                <td>{{ $tarif->classe?->nom ?? '—' }}</td>
                                <td>{{ \App\Support\Monnaie::format($tarif->montant_exige, $tarif->devise) }}</td>
                                <td>{{ \App\Support\Monnaie::normaliser($tarif->devise) }}</td>
                                <td>{{ $tarif->date_echeance?->format('d/m/Y') }}</td>
                                <td><span class="badge badge-{{ $tarif->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($tarif->statut) }}</span></td>
                                <td>
                                    <form class="d-inline" method="POST" action="{{ route('admin.frais.tarifs.destroy', $tarif) }}" onsubmit="return confirm('Supprimer ce tarif ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Aucun tarif pour l'année en cours.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
