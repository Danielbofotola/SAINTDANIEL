<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paie &amp; salaires | {{ $systemSettings->nom_court }}</title>
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
        .table-paie thead th { background:#cbd5e1; }
    </style>
</head>
<body class="p-3">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
<div class="container-fluid">

    <div class="caisse-card mb-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <div class="card-title-header mb-0"><i class="bi bi-cash-stack me-2"></i>Paie &amp; salaires</div>
            <small class="text-muted">{{ $systemSettings->nom_etablissement }} · salaires des professeurs et du personnel</small>
        </div>
        <div>
            <a href="{{ route('comptable.depenses.index') }}" class="btn btn-sm btn-outline-danger bg-white"><i class="bi bi-cart-dash me-1"></i> Dépenses</a>
            <a href="{{ route('comptable.dashboard') }}" class="btn btn-sm btn-secondary"><i class="bi bi-speedometer2 me-1"></i> Tableau de bord</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-2 mb-3">
        <div class="col-md-4"><div class="widget"><div class="text-muted">Masse salariale mensuelle</div><div class="valeur">{{ $money($masseSalariale) }}</div></div></div>
        <div class="col-md-4"><div class="widget"><div class="text-muted">Payé — {{ $moisCourant }}</div><div class="valeur text-success">{{ $money($totalMois) }}</div></div></div>
        <div class="col-md-4"><div class="widget"><div class="text-muted">Payé cette année</div><div class="valeur">{{ $money($totalAnnee) }}</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-people me-1"></i> Bénéficiaires &amp; salaire de base</div>
                <div class="table-responsive">
                    <table class="table table-sm table-paie align-middle mb-0">
                        <thead><tr><th>Bénéficiaire</th><th>Catégorie</th><th style="width:170px">Salaire de base</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($beneficiaires as $beneficiaire)
                                @php($cle = $beneficiaire['type'].':'.$beneficiaire['id'])
                                @php($salaire = $salaires[$cle] ?? null)
                                <tr>
                                    <td class="fw-bold">{{ $beneficiaire['nom'] }}</td>
                                    <td class="text-muted">{{ $beneficiaire['categorie'] }}</td>
                                    <td>
                                        <form class="d-flex" method="POST" action="{{ route('comptable.paies.salaires.store') }}">
                                            @csrf
                                            <input type="hidden" name="beneficiaire_type" value="{{ $beneficiaire['type'] }}">
                                            <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire['id'] }}">
                                            <input class="form-control form-control-sm" type="number" step="0.01" min="0" name="salaire_base" value="{{ old('salaire_base', $salaire?->salaire_base) }}" required>
                                            <button class="btn btn-sm btn-primary ms-1" type="submit"><i class="bi bi-save"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($salaire)
                                            <button class="btn btn-sm btn-info js-payer" type="button" data-salaire="{{ $salaire->id }}"><i class="bi bi-cash"></i> Payer</button>
                                        @else
                                            <span class="badge bg-secondary">Non défini</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun professeur ni personnel actif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-cash-coin me-1"></i> Payer un salaire</div>
                @if ($salaires->isEmpty())
                    <p class="text-muted mb-0">Définissez d'abord un salaire de base.</p>
                @else
                    <form method="POST" action="{{ route('comptable.paies.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-7"><label class="form-label mb-1 fw-bold">Bénéficiaire *</label>
                                <select class="form-select form-select-sm" name="salaire_id" id="salaireSelect" required>
                                    <option value="">Choisir</option>
                                    @foreach ($salaires as $salaire)
                                        <option value="{{ $salaire->id }}" data-base="{{ (float) $salaire->salaire_base }}" @selected(old('salaire_id') == $salaire->id)>{{ $salaire->nomBeneficiaire() }} — {{ $money($salaire->salaire_base) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5"><label class="form-label mb-1 fw-bold">Mois à payer *</label><input class="form-control form-control-sm" type="month" name="periode" value="{{ old('periode', $moisCourant) }}" required></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Primes</label><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="primes" id="primes" value="{{ old('primes', 0) }}"></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Retenues</label><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="retenues" id="retenues" value="{{ old('retenues', 0) }}"></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Date *</label><input class="form-control form-control-sm" type="text" data-date-fr name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}" required></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Mode *</label><select class="form-select form-select-sm" name="mode_paiement" required>@foreach ($modes as $valeur => $libelle)<option value="{{ $valeur }}" @selected(old('mode_paiement', 'especes') === $valeur)>{{ $libelle }}</option>@endforeach</select></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Référence</label><input class="form-control form-control-sm" name="reference" value="{{ old('reference') }}" maxlength="100"></div>
                            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Observation</label><input class="form-control form-control-sm" name="observation" value="{{ old('observation') }}" maxlength="255"></div>
                        </div>
                        <div class="alert alert-info py-2 my-2" id="netInfo">Net à payer : <strong>—</strong></div>
                        <button class="btn btn-success w-100 fw-bold" type="submit"><i class="bi bi-check2-circle me-1"></i> Enregistrer le paiement du salaire</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="caisse-card">
        <div class="card-title-header"><i class="bi bi-journal-text me-1"></i> Paiements de salaire ({{ $paies->count() }})</div>
        <div class="table-responsive">
            <table class="table table-sm table-paie align-middle mb-0">
                <thead><tr><th>N° paie</th><th>Bénéficiaire</th><th>Mois</th><th class="text-end">Base</th><th class="text-end">Primes</th><th class="text-end">Retenues</th><th class="text-end">Net</th><th>Payé le</th></tr></thead>
                <tbody>
                    @forelse ($paies as $paie)
                        <tr>
                            <td class="fw-bold">{{ $paie->num_paie }}</td>
                            <td>{{ $paie->salaire?->nomBeneficiaire() ?? '—' }}</td>
                            <td>{{ $paie->periode }}</td>
                            <td class="text-end">{{ $money($paie->montant_base) }}</td>
                            <td class="text-end">{{ $money($paie->primes) }}</td>
                            <td class="text-end">{{ $money($paie->retenues) }}</td>
                            <td class="text-end fw-bold">{{ $money($paie->montant_net) }}</td>
                            <td>{{ $paie->date_paiement?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucun paiement de salaire enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        var select = document.getElementById('salaireSelect');
        var primes = document.getElementById('primes');
        var retenues = document.getElementById('retenues');
        var info = document.getElementById('netInfo');

        function base() {
            if (!select || !select.value) { return 0; }
            var option = select.options[select.selectedIndex];
            return Number(option ? option.getAttribute('data-base') : 0) || 0;
        }

        function majNet() {
            if (!info) { return; }
            var net = Math.max(base() + (Number(primes.value) || 0) - (Number(retenues.value) || 0), 0);
            info.innerHTML = 'Salaire de base : <b>' + base().toFixed(2) + '</b> · Net à payer : <strong>' + net.toFixed(2) + '</strong>';
        }

        if (select) { select.addEventListener('change', majNet); }
        if (primes) { primes.addEventListener('input', majNet); }
        if (retenues) { retenues.addEventListener('input', majNet); }
        majNet();

        Array.prototype.forEach.call(document.querySelectorAll('.js-payer'), function (bouton) {
            bouton.addEventListener('click', function () {
                if (select) { select.value = bouton.getAttribute('data-salaire'); }
                majNet();
            });
        });
    })();
</script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
