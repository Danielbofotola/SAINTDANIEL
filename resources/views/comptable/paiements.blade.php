<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caisse — Encaisser un paiement | {{ $systemSettings->nom_court }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <style>
        :root { --bg-canvas:#d0dbe5; --card-bg:#f2f5f8; --card-border:#b0c2d0; --green-btn:#107c41; }
        body { background-color: var(--bg-canvas); font-family: 'Segoe UI', system-ui, sans-serif; color:#1e293b; font-size:.82rem; }
        .caisse-card { background: var(--card-bg); border:1px solid var(--card-border); border-radius:8px; padding:14px; box-shadow:0 2px 6px rgba(0,0,0,.06); height:100%; }
        .card-title-header { font-size:.95rem; font-weight:700; color:#0f172a; margin-bottom:8px; text-transform:uppercase; }
        .table-caisse thead th { background:#cbd5e1; }
    </style>
</head>
<body class="p-3">
@php($money = fn ($v, $d = 'USD') => \App\Support\Monnaie::format($v, $d))
<div class="container-fluid">

    <div class="caisse-card mb-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <div class="card-title-header mb-0"><i class="bi bi-cash-coin me-2"></i>Caisse — Encaisser un paiement</div>
            <small class="text-muted">{{ $systemSettings->nom_etablissement }}@if($annee) · année <strong>{{ $annee->libelle }}</strong>@endif · Total encaissé :
                @forelse ($totauxParDevise as $devise => $montant)<strong>{{ $money($montant, $devise) }}</strong>@if (! $loop->last) · @endif @empty <strong>{{ $money(0) }}</strong> @endforelse
            </small>
        </div>
        <div>
            <a href="{{ route('comptable.dashboard') }}" class="btn btn-sm btn-secondary"><i class="bi bi-speedometer2 me-1"></i> Tableau de bord de caisse</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @unless ($annee)<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>Aucune année scolaire en cours.</div>@endunless

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-search me-1"></i> Filtrer les élèves</div>
                <form class="row g-2 align-items-end mb-3" method="GET" action="{{ route('comptable.paiements.index') }}">
                    <div class="col-md-6"><label class="form-label mb-1 fw-bold">Classe</label>
                        <select class="form-select form-select-sm" name="classe_id">
                            <option value="">Toutes les classes</option>
                            @foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><button class="btn btn-sm btn-info text-white w-100"><i class="bi bi-funnel me-1"></i> Filtrer par classe</button></div>
                    <div class="col-md-2"><a href="{{ route('comptable.paiements.index') }}" class="btn btn-sm btn-outline-secondary w-100">Tout</a></div>
                </form>

                <div class="card-title-header"><i class="bi bi-cash-stack me-1"></i> Nouveau paiement</div>
                @if ($inscriptions->isEmpty() || $tarifs->isEmpty())
                    <p class="text-muted mb-0">Il faut au moins un élève inscrit et un tarif pour l'année en cours. Les tarifs se définissent dans « Types de frais ».</p>
                @else
                    <form method="POST" action="{{ route('comptable.paiements.store') }}">
                        @csrf
                        <input type="hidden" name="classe_id" value="{{ $classeId }}">
                        <div class="mb-2">
                            <label class="form-label mb-1 fw-bold">Rechercher l'élève</label>
                            <input class="form-control form-control-sm" type="text" id="eleveSearch" placeholder="Tapez un nom ou un matricule pour filtrer la liste…" autocomplete="off">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label mb-1 fw-bold">Élève *</label>
                                <select class="form-select form-select-sm" id="inscriptionSelect" name="inscription_id" required>
                                    <option value="">Choisir un élève</option>
                                    @foreach ($inscriptions as $inscription)<option value="{{ $inscription->id }}" @selected(old('inscription_id') == $inscription->id)>{{ $inscription->eleve?->nom }} {{ $inscription->eleve?->postnom }} {{ $inscription->eleve?->prenom }} — {{ $inscription->classe?->nom }} ({{ $inscription->eleve?->matricule }})</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label mb-1 fw-bold" id="tarifLabel">Tarif de la classe de l'élève *</label>
                                <select class="form-select form-select-sm" id="fraisSelect" name="frais_scolaire_id" required>
                                    <option value="">Choisir un tarif</option>
                                    @foreach ($tarifs as $tarif)<option value="{{ $tarif->id }}" @selected(old('frais_scolaire_id') == $tarif->id)>{{ $tarif->typeFrais?->nom }} — {{ $tarif->classe?->nom }} ({{ $money($tarif->montant_exige, $tarif->devise) }})</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-info d-none py-2 mt-2 mb-2" id="soldeInfo"></div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1 fw-bold">Période <span class="text-muted fw-normal">· automatique</span></label>
                                @if ($periodeCourante)
                                    <input type="hidden" name="periode_id" value="{{ $periodeCourante->id }}">
                                    <input class="form-control form-control-sm" value="{{ $periodeCourante->nom }}" readonly>
                                @else
                                    <select class="form-select form-select-sm" name="periode_id"><option value="">Non précisée</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}">{{ $periode->nom }}</option>@endforeach</select>
                                @endif
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold">Montant reçu *</label><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" id="montantRecu" name="montant_paye" value="{{ old('montant_paye') }}" required></div>
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold">Devise *</label><select class="form-select form-select-sm" id="deviseSelect" name="devise">@foreach ($devises as $code => $libelle)<option value="{{ $code }}" @selected(old('devise', 'USD') === $code)>{{ $libelle }}</option>@endforeach</select></div>
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold" title="Francs congolais pour 1 dollar">Taux (FC/1$)</label><input class="form-control form-control-sm" type="number" step="0.01" min="0" id="tauxInput" name="taux" value="{{ old('taux', $tauxParDefaut) }}"></div>
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold">Mode *</label><select class="form-select form-select-sm" name="mode_paiement" required>@foreach ($modes as $value => $label)<option value="{{ $value }}" @selected(old('mode_paiement', 'especes') === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold">Date</label><input class="form-control form-control-sm" type="text" data-date-fr name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}"></div>
                            <div class="col-md-2"><label class="form-label mb-1 fw-bold">Référence <span class="text-muted fw-normal">· auto</span></label><input class="form-control form-control-sm" value="N° de reçu" readonly title="Générée automatiquement à partir du numéro de reçu"></div>
                        </div>
                        <div class="alert alert-secondary py-2 mt-2 mb-0" id="equivalence"></div>
                        <button class="btn btn-success mt-3 w-100 fw-bold" type="submit" style="--green-btn:#107c41;background:var(--green-btn);" @disabled(! $annee)><i class="bi bi-check2-circle me-1"></i> Encaisser le paiement</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="caisse-card">
                <div class="card-title-header"><i class="bi bi-receipt me-1"></i> Derniers paiements</div>
                <div class="table-responsive">
                    <table class="table table-sm table-caisse align-middle mb-0">
                        <thead><tr><th>Reçu</th><th>Élève</th><th class="text-end">Montant</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($paiements as $paiement)
                                <tr>
                                    <td><span class="fw-bold">{{ $paiement->num_recu }}</span><br><small class="text-muted">{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</small></td>
                                    <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}<br><small class="text-muted">{{ $paiement->inscription?->classe?->nom }} · {{ $paiement->fraisScolaire?->typeFrais?->nom }}</small></td>
                                    <td class="text-end fw-bold">{{ $money($paiement->montant_paye, $paiement->fraisScolaire?->devise) }}@php($devFrais = \App\Support\Monnaie::normaliser($paiement->fraisScolaire?->devise))@if ($paiement->deviseRecue() !== $devFrais)<br><small class="text-muted">reçu {{ $money($paiement->montantRecu(), $paiement->deviseRecue()) }} @ {{ \App\Support\Monnaie::format($paiement->taux, $devFrais === 'CDF' ? 'USD' : 'CDF') }}</small>@endif</td>
                                    <td class="text-end"><a href="{{ route('comptable.paiements.recu', $paiement) }}" class="btn btn-sm btn-outline-info" title="Voir le reçu"><i class="bi bi-receipt"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun paiement enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var payeParPaire = @json($soldePaye);
        var montantsFrais = @json($montantsFrais);
        var classesParInscription = @json($classesParInscription ?? []);
        var classesParFrais = @json($classesParFrais ?? []);
        var tarifsDisponibles = @json($tarifsDisponibles ?? []);
        var nomsClasses = @json($nomsClasses ?? []);
        var recherche = document.getElementById('eleveSearch');
        var inscriptionSelect = document.getElementById('inscriptionSelect');
        var fraisSelect = document.getElementById('fraisSelect');
        var soldeInfo = document.getElementById('soldeInfo');

        function filtrerEleves() {
            if (!recherche || !inscriptionSelect) { return; }
            var terme = recherche.value.trim().toLowerCase();
            var correspondances = [];
            Array.prototype.forEach.call(inscriptionSelect.options, function (option) {
                if (!option.value) { option.hidden = false; return; }
                option.hidden = terme !== '' && option.text.toLowerCase().indexOf(terme) === -1;
                if (!option.hidden) { correspondances.push(option); }
            });

            // Scan d'une carte (QR) ou saisie d'un matricule : s'il ne reste qu'un
            // élève, on le sélectionne directement pour enchaîner l'encaissement.
            if (terme.length >= 4 && correspondances.length === 1) {
                inscriptionSelect.value = correspondances[0].value;
                inscriptionSelect.dispatchEvent(new Event('change'));
            }
        }

        var deviseSelect = document.getElementById('deviseSelect');
        var tauxInput = document.getElementById('tauxInput');
        var montantRecu = document.getElementById('montantRecu');
        var equivalence = document.getElementById('equivalence');
        var TAUX_DEFAUT = @json($tauxParDefaut);

        function deviseDeFrais(id) {
            var tarif = tarifsDisponibles.filter(function (t) { return String(t.id) === String(id); })[0];
            return tarif ? (tarif.devise || 'USD') : 'USD';
        }

        // USD : 2 décimales · CDF : aucune (les « zéros » du franc sont retirés).
        function fmt(v, d) {
            var n = (d === 'CDF') ? 0 : 2;
            return Number(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: n, maximumFractionDigits: n })
                + (d === 'CDF' ? ' FC' : ' $');
        }

        function convertir(m, de, vers, taux) {
            if (de === vers || !(taux > 0)) { return m; }
            return de === 'CDF' ? m / taux : m * taux;
        }

        function majSolde() {
            if (!inscriptionSelect || !fraisSelect || !soldeInfo) { return; }
            var inscription = inscriptionSelect.value;
            var frais = fraisSelect.value;
            if (!inscription || !frais) {
                soldeInfo.classList.add('d-none');
            } else {
                var d = deviseDeFrais(frais);
                var exige = Number(montantsFrais[frais] || 0);
                var paye = Number(payeParPaire[inscription + '-' + frais] || 0);
                soldeInfo.classList.remove('d-none');
                soldeInfo.innerHTML = 'Montant exigé : <b>' + fmt(exige, d) + '</b> · Déjà payé : <b>' + fmt(paye, d)
                    + '</b> · <b>Reste à payer : ' + fmt(Math.max(exige - paye, 0), d) + '</b>';

                // Pré-remplissage automatique : devise du tarif et montant = reste à payer.
                if (deviseSelect) { deviseSelect.value = d; }
                if (montantRecu) { montantRecu.value = Math.max(exige - paye, 0) > 0 ? Math.max(exige - paye, 0).toFixed(2) : ''; }
            }
            majEquivalence();
        }

        // Aperçu de la conversion : le montant remis dans sa devise → la devise du tarif.
        function majEquivalence() {
            if (!equivalence || !fraisSelect) { return; }
            var frais = fraisSelect.value;
            if (!frais) { equivalence.textContent = ''; return; }
            var dTarif = deviseDeFrais(frais);
            var dRecue = deviseSelect ? deviseSelect.value : dTarif;
            var taux = Number(tauxInput && tauxInput.value ? tauxInput.value : TAUX_DEFAUT) || TAUX_DEFAUT;
            var m = Number(montantRecu && montantRecu.value ? montantRecu.value : 0);
            var ref = convertir(m, dRecue, dTarif, taux);
            equivalence.textContent = 'Tarif en ' + dTarif + ' · ' + fmt(m, dRecue) + ' → ' + fmt(ref, dTarif)
                + (dRecue !== dTarif ? ' (taux ' + taux + ')' : '');
        }

        // Reconstruit la liste des tarifs : ne garde QUE ceux de la classe de
        // l'élève sélectionné (les tarifs des autres classes sont retirés du DOM,
        // et non simplement masqués).
        function rendreTarifs() {
            if (!fraisSelect) { return; }

            var inscription = inscriptionSelect ? inscriptionSelect.value : '';
            var classe = inscription ? String(classesParInscription[inscription] || '') : '';
            var courant = fraisSelect.value;
            var disponibles = tarifsDisponibles.filter(function (tarif) {
                return classe === '' || String(tarif.classe) === classe;
            });

            var label = document.getElementById('tarifLabel');
            if (label) {
                label.textContent = (classe !== '' && nomsClasses[classe])
                    ? 'Tarif * (classe ' + nomsClasses[classe] + ')'
                    : "Tarif de la classe de l'élève *";
            }

            fraisSelect.innerHTML = '';
            var vide = document.createElement('option');
            vide.value = '';
            vide.textContent = classe !== '' && nomsClasses[classe]
                ? 'Choisir un tarif de ' + nomsClasses[classe]
                : 'Choisir un tarif';
            fraisSelect.appendChild(vide);

            disponibles.forEach(function (tarif) {
                var option = document.createElement('option');
                option.value = tarif.id;
                option.textContent = tarif.libelle;
                fraisSelect.appendChild(option);
            });

            var encoreValide = disponibles.some(function (tarif) { return String(tarif.id) === String(courant); });
            if (encoreValide) {
                fraisSelect.value = courant;
            } else if (disponibles.length === 1) {
                fraisSelect.value = disponibles[0].id;
            }

            majSolde();
        }

        if (recherche) { recherche.addEventListener('input', filtrerEleves); }
        if (inscriptionSelect) { inscriptionSelect.addEventListener('change', rendreTarifs); }
        if (fraisSelect) { fraisSelect.addEventListener('change', majSolde); }
        if (deviseSelect) { deviseSelect.addEventListener('change', majEquivalence); }
        if (tauxInput) { tauxInput.addEventListener('input', majEquivalence); }
        if (montantRecu) { montantRecu.addEventListener('input', majEquivalence); }
        rendreTarifs();
    })();
</script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
