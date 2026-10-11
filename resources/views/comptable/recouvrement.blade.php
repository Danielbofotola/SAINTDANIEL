<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recouvrement | {{ $systemSettings->nom_court }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <style>
        :root { --bg-canvas:#d0dbe5; --card-bg:#f2f5f8; --card-border:#b0c2d0; }
        body { background: var(--bg-canvas); font-family:'Segoe UI', system-ui, sans-serif; color:#1e293b; font-size:.82rem; }
        .caisse-card { background: var(--card-bg); border:1px solid var(--card-border); border-radius:8px; padding:14px; box-shadow:0 2px 6px rgba(0,0,0,.06); height:100%; }
        .card-title-header { font-size:.95rem; font-weight:700; color:#0f172a; margin-bottom:8px; text-transform:uppercase; }
        .widget { background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; text-align:center; }
        .widget .valeur { font-size:1.35rem; font-weight:700; }
        .barre { height:16px; background:#e2e8f0; border-radius:8px; overflow:hidden; }
        .barre > span { display:block; height:100%; background:#107c41; }
         div.dataTables_wrapper .dataTables_filter input { border:1px solid #94a3b8; border-radius:6px; padding:3px 8px; }
         table.dataTable thead th { background:#cbd5e1; }
    </style>
</head>
<body class="p-3">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
@php($maxPeriode = $parPeriode->max() ?: 0)
@php($maxMois = $parMois->max() ?: 0)
<div class="container-fluid">
    <div class="caisse-card mb-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <div class="card-title-header mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Recouvrement &amp; évolution des paiements</div>
            <small class="text-muted">{{ $systemSettings->nom_etablissement }}@if($annee) · année <strong>{{ $annee->libelle }}</strong>@endif</small>
        </div>
        <div>
            <a href="{{ route('comptable.paiements.index') }}" class="btn btn-sm btn-success"><i class="bi bi-cash-coin me-1"></i> Encaisser un paiement</a>
            <a href="{{ route('comptable.dashboard') }}" class="btn btn-sm btn-secondary"><i class="bi bi-speedometer2 me-1"></i> Tableau de bord</a>
        </div>
    </div>

    <div class="caisse-card mb-3">
        <form class="row g-2 align-items-end" method="GET" action="{{ route('comptable.recouvrement.index') }}">
            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Classe</label><select class="form-select form-select-sm" name="classe_id"><option value="">Toutes les classes</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label mb-1 fw-bold">Période</label><select class="form-select form-select-sm" name="periode_id"><option value="">Toutes les périodes</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}" @selected((string) $periodeId === (string) $periode->id)>{{ $periode->nom }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-sm btn-info text-white w-100"><i class="bi bi-funnel me-1"></i> Filtrer</button></div>
            <div class="col-md-2"><a href="{{ route('comptable.recouvrement.index') }}" class="btn btn-sm btn-outline-secondary w-100">Tout</a></div>
        </form>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="widget"><div class="text-muted">Frais définis</div><div class="valeur">{{ $money($definis) }}</div></div></div>
        <div class="col-md-3"><div class="widget"><div class="text-muted">Encaissé</div><div class="valeur text-success">{{ $money($encaisse) }}</div></div></div>
        <div class="col-md-3"><div class="widget"><div class="text-muted">Reste à percevoir</div><div class="valeur text-danger">{{ $money($reste) }}</div></div></div>
        <div class="col-md-3"><div class="widget"><div class="text-muted">Taux de recouvrement</div><div class="valeur">{{ $taux }} %</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-calendar3 me-1"></i> Évolution par période</div>
                @forelse ($parPeriode as $periode => $montant)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span class="fw-bold">{{ $periode }}</span><span>{{ $money($montant) }}</span></div>
                        <div class="barre"><span style="width: {{ $maxPeriode > 0 ? round($montant / $maxPeriode * 100) : 0 }}%"></span></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun paiement sur cette sélection.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-calendar-month me-1"></i> Évolution par mois</div>
                @forelse ($parMois as $mois => $montant)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span class="fw-bold">{{ $mois }}</span><span>{{ $money($montant) }}</span></div>
                        <div class="barre"><span style="width: {{ $maxMois > 0 ? round($montant / $maxMois * 100) : 0 }}%"></span></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun paiement sur cette sélection.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="caisse-card mb-3">
        <div class="card-title-header"><i class="bi bi-buildings me-1"></i> Recouvrement par classe</div>
        @if ($parClasse->isNotEmpty())
            <div class="table-responsive">
                <table id="tableRecouvrement" class="table table-sm align-middle mb-0" style="width:100%">
                    <thead><tr><th>Classe</th><th class="text-end">Défini</th><th class="text-end">Encaissé</th><th class="text-end">Reste</th><th style="width:220px">Avancement</th></tr></thead>
                    <tbody>
                        @foreach ($parClasse as $ligne)
                            @php($pct = $ligne->defini > 0 ? min(100, round($ligne->encaisse / $ligne->defini * 100)) : 0)
                            <tr>
                                <td class="fw-bold">{{ $ligne->nom }}</td>
                                <td class="text-end">{{ $money($ligne->defini) }}</td>
                                <td class="text-end text-success">{{ $money($ligne->encaisse) }}</td>
                                <td class="text-end text-danger">{{ $money(max($ligne->defini - $ligne->encaisse, 0)) }}</td>
                                <td><div class="barre"><span style="width: {{ $pct }}%"></span></div><small class="text-muted">{{ $pct }} %</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Aucune donnée de recouvrement sur cette sélection.</p>
        @endif
    </div>

    <div class="caisse-card">
        <div class="card-title-header"><i class="bi bi-receipt-cutoff me-1"></i> Détail des paiements ({{ $paiements->count() }})</div>
        <div class="table-responsive">
            <table id="tablePaiements" class="table table-sm align-middle mb-0" style="width:100%">
                <thead><tr><th>Reçu</th><th>Date</th><th>Élève</th><th>Classe</th><th>Tarif</th><th>Période</th><th class="text-end">Montant</th><th></th></tr></thead>
                <tbody>
                    @foreach ($paiements as $paiement)
                        <tr>
                            <td class="fw-bold">{{ $paiement->num_recu }}</td>
                            <td>{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</td>
                            <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}</td>
                            <td>{{ $paiement->inscription?->classe?->nom }}</td>
                            <td>{{ $paiement->fraisScolaire?->typeFrais?->nom }}</td>
                            <td>{{ $paiement->periode?->nom ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ $money($paiement->montant_paye) }}</td>
                            <td class="text-end"><a href="{{ route('comptable.paiements.recu', $paiement) }}" class="btn btn-sm btn-outline-info" title="Voir le reçu"><i class="bi bi-receipt"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    jQuery(function ($) {
        if (!$.fn.dataTable) { return; }
        var langueFr = {
            search: 'Rechercher :',
            lengthMenu: 'Afficher _MENU_ lignes',
            info: '_START_ à _END_ sur _TOTAL_ paiement(s)',
            infoEmpty: 'Aucune ligne',
            infoFiltered: '(filtré sur _MAX_ lignes)',
            zeroRecords: 'Aucun résultat',
            paginate: { first: 'Premier', last: 'Dernier', next: 'Suivant', previous: 'Précédent' }
        };
        var base = {
            language: langueFr,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Tout']],
            order: [],
            autoWidth: false,
            dom: "<'row mb-2'<'col-md-6'l><'col-md-6'f>>t<'row mt-2'<'col-md-5'i><'col-md-7'p>>"
        };
        if ($('#tableRecouvrement').length) {
            $('#tableRecouvrement').DataTable($.extend({}, base, { columnDefs: [{ orderable: false, targets: 4 }] }));
        }
        if ($('#tablePaiements').length) {
            $('#tablePaiements').DataTable($.extend({}, base, { columnDefs: [{ orderable: false, targets: 7 }] }));
        }
    });
</script>
</body>
</html>
