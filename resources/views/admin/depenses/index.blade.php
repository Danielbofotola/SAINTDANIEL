<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dépenses</title>
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
                <h1><i class="fa fa-shopping-cart"></i> Dépenses</h1>
                <p>Enregistrez les dépenses de l'établissement par catégorie @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.paiements.index') }}" class="btn btn-secondary">Paiements</a>
                <a href="{{ route('admin.archives.index') }}" class="btn btn-primary"><i class="fa fa-archive"></i> Clôture &amp; archives</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours.</div>@endunless
        @include('partials.alertes', ['alertes' => $alertes])

        <div class="row">
            <div class="col-md-4"><div class="widget-small danger coloured-icon"><i class="icon fa fa-shopping-cart fa-3x"></i><div class="info"><h4>Total dépenses</h4><p><b>{{ number_format($totalDepenses, 2, ',', ' ') }}</b></p></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-plus"></i> Nouvelle dépense</h4>
                    @if ($categoriesActives->isEmpty())
                        <p class="text-muted mb-0">Créez d'abord une catégorie de dépenses.</p>
                    @else
                        <form method="POST" action="{{ route('admin.depenses.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group"><label class="form-label">Catégorie *</label><select class="form-control" name="category_id" required><option value="">Choisir</option>@foreach ($categoriesActives as $categorie)<option value="{{ $categorie->id }}" @selected(old('category_id') == $categorie->id)>{{ $categorie->nom }}</option>@endforeach</select></div>
                                <div class="col-md-6 form-group"><label class="form-label">Montant *</label><input class="form-control" type="number" step="0.01" min="0.01" name="montant" value="{{ old('montant') }}" required></div>
                                <div class="col-md-12 form-group"><label class="form-label">Motif *</label><input class="form-control" name="motif" value="{{ old('motif') }}" maxlength="255" required></div>
                                <div class="col-md-6 form-group"><label class="form-label">Date *</label><input class="form-control" type="text" data-date-fr name="date_depense" value="{{ old('date_depense', now()->toDateString()) }}" required></div>
                                <div class="col-md-6 form-group"><label class="form-label">Pièce justificative</label><input class="form-control" name="piece_justificative" value="{{ old('piece_justificative') }}" maxlength="255" placeholder="Référence (facultatif)"></div>
                            </div>
                            <button class="btn btn-primary" type="submit" @disabled(! $annee)><i class="fa fa-save"></i> Enregistrer la dépense</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-tags"></i> Catégories de dépenses</h4>
                    <form class="row" method="POST" action="{{ route('admin.depenses.categories.store') }}">
                        @csrf
                        <div class="col-md-8 form-group mb-md-0"><input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="100" required placeholder="Ex. Fournitures, Entretien…"></div>
                        <div class="col-md-4"><button class="btn btn-success btn-block" type="submit"><i class="fa fa-plus"></i> Ajouter</button></div>
                    </form>
                    <hr>
                    @forelse ($categories as $categorie)
                        <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                            <span><strong>{{ $categorie->nom }}</strong> <small class="text-muted">({{ $categorie->depenses_count }} dépense(s))</small></span>
                            <form method="POST" action="{{ route('admin.depenses.categories.destroy', $categorie) }}" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune catégorie enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Dépenses @if($annee)<small class="text-muted">{{ $annee->libelle }}</small>@endif</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Date</th><th>Catégorie</th><th>Motif</th><th>Montant</th><th>Pièce</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($depenses as $depense)
                            <tr>
                                <td>{{ $depenses->firstItem() + $loop->index }}</td>
                                <td>{{ $depense->date_depense?->format('d/m/Y') }}</td>
                                <td>{{ $depense->categorie?->nom ?? '—' }}</td>
                                <td>{{ $depense->motif }}</td>
                                <td>{{ number_format((float) $depense->montant, 2, ',', ' ') }}</td>
                                <td>{{ $depense->piece_justificative ?: '—' }}</td>
                                <td>
                                    <form class="d-inline" method="POST" action="{{ route('admin.depenses.destroy', $depense) }}" onsubmit="return confirm('Supprimer cette dépense ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Aucune dépense enregistrée pour l'année en cours.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $depenses->total() }} dépense(s) — page {{ $depenses->currentPage() }} / {{ $depenses->lastPage() }}</small>
                {{ $depenses->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
