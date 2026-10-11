<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dépenses | {{ $systemSettings->nom_court }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <style>
        :root { --bg-canvas:#d0dbe5; --card-bg:#f2f5f8; --card-border:#b0c2d0; }
        body { background: var(--bg-canvas); font-family:'Segoe UI', system-ui, sans-serif; color:#1e293b; font-size:.82rem; }
        .caisse-card { background: var(--card-bg); border:1px solid var(--card-border); border-radius:8px; padding:14px; box-shadow:0 2px 6px rgba(0,0,0,.06); height:100%; }
        .card-title-header { font-size:.95rem; font-weight:700; color:#0f172a; margin-bottom:8px; text-transform:uppercase; }
        .widget { background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; text-align:center; }
        .widget .valeur { font-size:1.35rem; font-weight:700; }
        .table-depenses thead th { background:#cbd5e1; }
    </style>
</head>
<body class="p-3">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
<div class="container-fluid">

    <div class="caisse-card mb-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <div class="card-title-header mb-0"><i class="bi bi-cart-dash me-2"></i>Caisse — Dépenses</div>
            <small class="text-muted">{{ $systemSettings->nom_etablissement }}@if($annee) · année <strong>{{ $annee->libelle }}</strong>@endif</small>
        </div>
        <div>
            <a href="{{ route('comptable.paiements.index') }}" class="btn btn-sm btn-success"><i class="bi bi-cash-coin me-1"></i> Encaisser un paiement</a>
            <a href="{{ route('comptable.dashboard') }}" class="btn btn-sm btn-secondary"><i class="bi bi-speedometer2 me-1"></i> Tableau de bord</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @unless ($annee)<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>Aucune année scolaire en cours.</div>@endunless
    @include('partials.alertes', ['alertes' => $alertes])

    <div class="row g-2 mb-3">
        <div class="col-md-4"><div class="widget"><div class="text-muted">Total dépenses de l’année</div><div class="valeur text-danger">{{ $money($total) }}</div></div></div>
        <div class="col-md-4"><div class="widget"><div class="text-muted">Dépenses de ce mois</div><div class="valeur">{{ $money($moisCourant) }}</div></div></div>
        <div class="col-md-4"><div class="widget"><div class="text-muted">Nombre d’écritures</div><div class="valeur">{{ $depenses->count() }}</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-cart-plus me-1"></i> Enregistrer une dépense</div>
                @if ($natures->isEmpty())
                    <p class="text-muted mb-0">Créez d’abord une <strong>nature de dépense</strong> (fournitures, entretien, salaires…), à droite.</p>
                @else
                    <form method="POST" action="{{ route('comptable.depenses.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label mb-1 fw-bold">Nature de la dépense *</label>
                                <select class="form-select form-select-sm" name="nature_id" required>
                                    <option value="">Choisir une nature</option>
                                    @foreach ($natures as $nature)<option value="{{ $nature->id }}" @selected(old('nature_id') == $nature->id)>{{ $nature->nom }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label mb-1 fw-bold">Motif *</label><input class="form-control form-control-sm" name="motif" value="{{ old('motif') }}" required maxlength="255" placeholder="Ex. Achat de craies"></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Montant *</label><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="montant" value="{{ old('montant') }}" required></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Date *</label><input class="form-control form-control-sm" type="text" data-date-fr name="date_depense" value="{{ old('date_depense', now()->toDateString()) }}" required></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Pièce justificative</label><input class="form-control form-control-sm" name="piece_justificative" value="{{ old('piece_justificative') }}" maxlength="255" placeholder="N° facture / reçu"></div>
                        </div>
                        <button class="btn btn-danger mt-3 w-100 fw-bold" type="submit" @disabled(! $annee)><i class="bi bi-check2-circle me-1"></i> Enregistrer la dépense</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-tags me-1"></i> Nouvelle nature de dépense</div>
                <form class="row g-2 align-items-end" method="POST" action="{{ route('comptable.depenses.natures.store') }}">
                    @csrf
                    <div class="col-8"><label class="form-label mb-1 fw-bold">Nom de la nature *</label><input class="form-control form-control-sm" name="nom" value="{{ old('nom') }}" required maxlength="100" placeholder="Ex. Fournitures de bureau"></div>
                    <div class="col-4"><button class="btn btn-sm btn-outline-primary bg-white w-100 fw-bold" type="submit"><i class="bi bi-plus-lg me-1"></i> Ajouter</button></div>
                </form>
                <hr>
                <h5 class="mb-2">Total par nature</h5>
                <ul class="list-unstyled mb-0">
                    @forelse ($parNature as $nature => $montant)
                        <li class="d-flex justify-content-between border-bottom py-1"><span>{{ $nature }}</span><strong>{{ $money($montant) }}</strong></li>
                    @empty
                        <li class="text-muted">Aucune dépense enregistrée.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="caisse-card">
        <div class="card-title-header"><i class="bi bi-journal-text me-1"></i> Dépenses enregistrées ({{ $depenses->count() }})</div>
        <div class="table-responsive">
            <table class="table table-sm table-depenses align-middle mb-0">
                <thead><tr><th>Date</th><th>Nature</th><th>Motif</th><th>Pièce</th><th class="text-end">Montant</th><th></th></tr></thead>
                <tbody>
                    @forelse ($depenses as $depense)
                        <tr>
                            <td>{{ $depense->date_depense?->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary">{{ $depense->categorie?->nom ?? '—' }}</span></td>
                            <td>{{ $depense->motif }}</td>
                            <td>{{ $depense->piece_justificative ?: '—' }}</td>
                            <td class="text-end fw-bold">{{ $money($depense->montant) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('comptable.depenses.destroy', $depense) }}" onsubmit="return confirm('Supprimer cette dépense ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune dépense enregistrée pour l’année en cours.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
