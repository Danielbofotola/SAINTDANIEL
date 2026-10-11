<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Décisions exécutives &amp; injonctions</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-gavel"></i> Décisions exécutives &amp; injonctions</h1>
                <p>Publiez une décision ou une injonction : elle est horodatée et conservée au journal d'audit @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('prefecture.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('prefecture.flux.index') }}" class="btn btn-primary"><i class="fa fa-toggle-on"></i> Flux académique</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-pen"></i> Nouvelle publication</h4>
            <form class="row" method="POST" action="{{ route('prefecture.decisions.store') }}">
                @csrf
                <div class="col-md-3 form-group"><label class="form-label">Type *</label><select class="form-control" name="type" required>@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(old('type', 'decision') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-6 form-group"><label class="form-label">Objet *</label><input class="form-control" name="objet" value="{{ old('objet') }}" maxlength="160" required></div>
                <div class="col-md-3 form-group"><label class="form-label">Échéance</label><input class="form-control" type="text" data-date-fr name="echeance" value="{{ old('echeance') }}" placeholder="jj/mm/aaaa"></div>
                <div class="col-md-4 form-group"><label class="form-label">Destinataires</label><input class="form-control" name="destinataires" value="{{ old('destinataires') }}" maxlength="255" placeholder="Ex. Enseignants, Secrétariat"></div>
                <div class="col-md-8 form-group"><label class="form-label">Texte *</label><textarea class="form-control" name="corps" rows="4" maxlength="5000" required>{{ old('corps') }}</textarea></div>
                <div class="col-md-12"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Publier et tracer</button></div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-book"></i> Journal des décisions</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Publiée le</th><th>Type</th><th>Objet</th><th>Destinataires</th><th>Auteur</th><th>Échéance</th></tr></thead>
                    <tbody>
                        @forelse ($decisions as $decision)
                            <tr>
                                <td>{{ $decision->publie_le?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td><span class="badge badge-{{ $decision->type === 'injonction' ? 'warning' : 'info' }}">{{ $decision->libelleType() }}</span></td>
                                <td><strong>{{ $decision->objet }}</strong><div class="text-muted small">{{ \Illuminate\Support\Str::limit($decision->corps, 120) }}</div></td>
                                <td>{{ $decision->destinataires ?: '—' }}</td>
                                <td>{{ trim(($decision->auteur?->prenom ?? '').' '.($decision->auteur?->nom ?? '')) ?: ($decision->auteur?->username ?? '—') }}</td>
                                <td>{{ $decision->echeance?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune décision publiée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $decisions->links('pagination::bootstrap-4') }}
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
