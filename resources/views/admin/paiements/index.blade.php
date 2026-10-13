<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiements</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>
<body class="app">
    @php($money = fn ($v, $d = 'USD') => \App\Support\Monnaie::format($v, $d))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-credit-card"></i> Paiements</h1>
                <p>Enregistrez les règlements et suivez les recettes @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.frais.index') }}" class="btn btn-secondary">Types de frais</a>
                <a href="{{ route('admin.depenses.index') }}" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> Dépenses</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-md-4"><div class="widget-small primary coloured-icon"><i class="icon fa fa-money fa-3x"></i><div class="info"><h4>Total encaissé</h4><p><b>@forelse ($totauxParDevise as $devise => $montant){{ $money($montant, $devise) }}@if (! $loop->last)<br>@endif @empty {{ $money(0) }} @endforelse</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small info coloured-icon"><i class="icon fa fa-list fa-3x"></i><div class="info"><h4>Paiements</h4><p><b>{{ $paiements->total() }}</b></p></div></div></div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouveau paiement</h4>
            @if ($inscriptions->isEmpty() || $tarifs->isEmpty())
                <p class="text-muted mb-0">Il faut au moins un élève inscrit et un tarif pour l'année en cours. Créez d'abord les tarifs dans « Types de frais ».</p>
            @else
                <form method="POST" action="{{ route('admin.paiements.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 form-group"><label class="form-label">Rechercher l'élève</label><input class="form-control" type="text" id="eleveSearch" placeholder="Tapez un nom pour filtrer la liste…" autocomplete="off"></div>
                        <div class="col-md-4 form-group"><label class="form-label">Élève inscrit *</label><select class="form-control" id="inscriptionSelect" name="inscription_id" required><option value="">Choisir un élève</option>@foreach ($inscriptions as $inscription)<option value="{{ $inscription->id }}" @selected(old('inscription_id') == $inscription->id)>{{ $inscription->eleve?->nom }} {{ $inscription->eleve?->postnom }} {{ $inscription->eleve?->prenom }} — {{ $inscription->classe?->nom }}</option>@endforeach</select></div>
                        <div class="col-md-4 form-group"><label class="form-label" id="tarifLabel">Tarif *</label><select class="form-control" id="fraisSelect" name="frais_scolaire_id" required><option value="">Choisir un tarif</option>@foreach ($tarifs as $tarif)<option value="{{ $tarif->id }}" @selected(old('frais_scolaire_id') == $tarif->id)>{{ $tarif->typeFrais?->nom }} — {{ $tarif->classe?->nom }} ({{ $money((float) $tarif->montant_exige, $tarif->devise) }})</option>@endforeach</select></div>
                        <div class="col-md-12"><div class="alert alert-info d-none" id="soldeInfo"></div></div>
                        <div class="col-md-2 form-group"><label class="form-label">Période · auto</label>
                            @if ($periodeCourante)
                                <input type="hidden" name="periode_id" value="{{ $periodeCourante->id }}">
                                <input class="form-control" value="{{ $periodeCourante->nom }}" readonly>
                            @else
                                <select class="form-control" name="periode_id"><option value="">Non précisée</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}">{{ $periode->nom }}</option>@endforeach</select>
                            @endif
                        </div>
                        <div class="col-md-2 form-group"><label class="form-label">Montant reçu *</label><input class="form-control" type="number" step="0.01" min="0.01" id="montantRecu" name="montant_paye" value="{{ old('montant_paye') }}" required></div>
                        <div class="col-md-2 form-group"><label class="form-label">Devise *</label><select class="form-control" id="deviseSelect" name="devise">@foreach ($devises as $code => $libelle)<option value="{{ $code }}" @selected(old('devise', 'USD') === $code)>{{ $libelle }}</option>@endforeach</select></div>
                        <div class="col-md-2 form-group"><label class="form-label" title="Francs congolais pour 1 dollar">Taux (FC/1$)</label><input class="form-control" type="number" step="0.01" min="0" name="taux" value="{{ old('taux', $tauxParDefaut) }}"></div>
                        <div class="col-md-2 form-group"><label class="form-label">Date</label><input class="form-control" type="text" data-date-fr name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}"></div>
                        <div class="col-md-2 form-group"><label class="form-label">Mode *</label><select class="form-control" name="mode_paiement" required>@foreach ($modes as $value => $label)<option value="{{ $value }}" @selected(old('mode_paiement', 'especes') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-4 form-group"><label class="form-label">Référence · auto</label><input class="form-control" value="N° de reçu" readonly title="Générée automatiquement à partir du numéro de reçu"></div>
                        <div class="col-md-8 form-group"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer le paiement</button></div>
                    </div>
                </form>
            @endif
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Paiements enregistrés</h4>
            <form class="row mb-3" method="GET" action="{{ route('admin.paiements.index') }}">
                <div class="col-md-5 form-group mb-md-0"><input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Rechercher par reçu, nom ou matricule…"></div>
                <div class="col-md-3 form-group mb-md-0"><select class="form-control" name="classe_id"><option value="">Toutes les classes</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                <div class="col-md-2 form-group mb-md-0"><select class="form-control" name="periode_id"><option value="">Toutes les périodes</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}" @selected((string) $periodeId === (string) $periode->id)>{{ $periode->nom }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Reçu</th><th>Date</th><th>Élève</th><th>Classe</th><th>Type de frais</th><th>Période</th><th>Montant exigé</th><th>Payé</th><th>Reste à payer</th><th>Mode</th><th>Référence</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($paiements as $paiement)
                            @php($cle = $paiement->inscription_id.'-'.$paiement->frais_scolaire_id)
                            @php($exige = (float) ($paiement->fraisScolaire?->montant_exige ?? 0))
                            @php($paye = (float) ($soldePaye[$cle] ?? 0))
                            @php($reste = max($exige - $paye, 0))
                            <tr>
                                <td><strong>{{ $paiement->num_recu }}</strong></td>
                                <td>{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</td>
                                <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}<br><small class="text-muted">{{ $paiement->inscription?->eleve?->matricule }}</small></td>
                                <td>{{ $paiement->inscription?->classe?->nom }}</td>
                                <td>{{ $paiement->fraisScolaire?->typeFrais?->nom }}</td>
                                <td>{{ $paiement->periode?->nom ?? '—' }}</td>
                                <td>{{ $money($exige, $paiement->fraisScolaire?->devise) }}</td>
                                <td>{{ $money((float) $paiement->montant_paye, $paiement->fraisScolaire?->devise) }}@if ($paiement->deviseRecue() !== \App\Support\Monnaie::normaliser($paiement->fraisScolaire?->devise))<br><small class="text-muted">reçu {{ $money($paiement->montantRecu(), $paiement->deviseRecue()) }}</small>@endif</td>
                                <td>
                                    @if ($reste <= 0)
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Soldé</span>
                                    @else
                                        <span class="badge badge-warning">{{ $money($reste, $paiement->fraisScolaire?->devise) }}</span>
                                    @endif
                                </td>
                                <td>{{ $modes[$paiement->mode_paiement] ?? $paiement->mode_paiement }}</td>
                                <td>{{ $paiement->reference_transaction ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.paiements.recu', $paiement) }}" class="btn btn-sm btn-info" title="Voir le reçu"><i class="fa fa-file-text-o"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.paiements.destroy', $paiement) }}" onsubmit="return confirm('Supprimer ce paiement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center text-muted">Aucun paiement pour cette sélection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $paiements->total() }} paiement(s) — page {{ $paiements->currentPage() }} / {{ $paiements->lastPage() }}</small>
                {{ $paiements->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>

    <script>
        (function () {
            var payeParPaire = @json($soldePaye);
            var montantsFrais = @json($montantsFrais);
            var recherche = document.getElementById('eleveSearch');
            var inscriptionSelect = document.getElementById('inscriptionSelect');
            var fraisSelect = document.getElementById('fraisSelect');
            var soldeInfo = document.getElementById('soldeInfo');
            var classesParInscription = @json($classesParInscription ?? []);
            var tarifsDisponibles = @json($tarifsDisponibles ?? []);
            var nomsClasses = @json($nomsClasses ?? []);

            function filtrerEleves() {
                if (!recherche || !inscriptionSelect) {
                    return;
                }
                var terme = recherche.value.trim().toLowerCase();
                Array.prototype.forEach.call(inscriptionSelect.options, function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }
                    option.hidden = terme !== '' && option.text.toLowerCase().indexOf(terme) === -1;
                });
            }

            function majSolde() {
                if (!inscriptionSelect || !fraisSelect || !soldeInfo) {
                    return;
                }
                var inscription = inscriptionSelect.value;
                var frais = fraisSelect.value;
                if (!inscription || !frais) {
                    soldeInfo.classList.add('d-none');
                    return;
                }
                var exige = Number(montantsFrais[frais] || 0);
                var paye = Number(payeParPaire[inscription + '-' + frais] || 0);
                var reste = Math.max(exige - paye, 0);
                soldeInfo.classList.remove('d-none');
                soldeInfo.innerHTML = 'Montant exigé : <b>' + exige.toFixed(2) + '</b> · Déjà payé : <b>' + paye.toFixed(2) + '</b> · Reste à payer : <b>' + reste.toFixed(2) + '</b>';

                 // Pré-remplissage automatique : devise du tarif et montant = reste à payer.
                 var deviseParFrais = @json($deviseParFrais ?? []);
                 var deviseSelect = document.getElementById('deviseSelect');
                 var montantInput = document.getElementById('montantRecu');
                 if (deviseSelect && deviseParFrais[frais]) { deviseSelect.value = deviseParFrais[frais]; }
                 if (montantInput) { montantInput.value = reste > 0 ? reste.toFixed(2) : ''; }
            }

            // Reconstruit la liste des tarifs : ne garde QUE ceux de la classe de
            // l'élève sélectionné (les tarifs des autres classes sont retirés).
            function rendreTarifs() {
                if (!fraisSelect) {
                    return;
                }
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
                vide.textContent = (classe !== '' && nomsClasses[classe])
                    ? 'Choisir un tarif de ' + nomsClasses[classe]
                    : 'Choisir un tarif';
                fraisSelect.appendChild(vide);

                disponibles.forEach(function (tarif) {
                    var option = document.createElement('option');
                    option.value = tarif.id;
                    option.textContent = tarif.libelle;
                    fraisSelect.appendChild(option);
                });

                if (disponibles.some(function (tarif) { return String(tarif.id) === String(courant); })) {
                    fraisSelect.value = courant;
                } else if (disponibles.length === 1) {
                    fraisSelect.value = disponibles[0].id;
                }

                majSolde();
            }

            if (recherche) {
                recherche.addEventListener('input', filtrerEleves);
            }
            if (inscriptionSelect) {
                inscriptionSelect.addEventListener('change', rendreTarifs);
            }
            if (fraisSelect) {
                fraisSelect.addEventListener('change', majSolde);
            }
            rendreTarifs();
        })();
    </script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
