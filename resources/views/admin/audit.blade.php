<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Journal d'audit</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-history"></i> Journal d'audit</h1>
                <p>Chronologie des décisions et injonctions publiées par la direction (auteur, cible, horodatage).</p>
            </div>
            <div><a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a></div>
        </div>

        <div class="tile">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Publiée le</th><th>Type</th><th>Objet / texte</th><th>Destinataires</th><th>Auteur</th><th>Année</th><th>Échéance</th></tr></thead>
                    <tbody>
                        @forelse ($decisions as $decision)
                            <tr>
                                <td>{{ $decisions->firstItem() + $loop->index }}</td>
                                <td>{{ $decision->publie_le?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td><span class="badge badge-{{ $decision->type === 'injonction' ? 'warning' : 'info' }}">{{ $decision->libelleType() }}</span></td>
                                <td><strong>{{ $decision->objet }}</strong><div class="text-muted small">{{ $decision->corps }}</div></td>
                                <td>{{ $decision->destinataires ?: '—' }}</td>
                                <td>{{ trim(($decision->auteur?->prenom ?? '').' '.($decision->auteur?->nom ?? '')) ?: ($decision->auteur?->username ?? '—') }}</td>
                                <td>{{ $decision->anneeScolaire?->libelle ?? '—' }}</td>
                                <td>{{ $decision->echeance?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Journal vide : aucune décision publiée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $decisions->links('pagination::bootstrap-4') }}
        </div>
    </main>
</body>
</html>
