<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paie &amp; salaires</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
@php($money = fn ($v) => number_format((float) $v, 2, ',', ' '))
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-money"></i> Paie &amp; salaires</h1>
                <p>Salaires des professeurs et du personnel, et paiements par mois.</p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.depenses.index') }}">Dépenses</a>
                <a class="btn btn-info" href="{{ route('admin.paiements.index') }}">Paiements élèves</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-md-4"><div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Masse salariale</h4><p><b>{{ $money($masseSalariale) }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small info coloured-icon"><i class="icon fa fa-calendar fa-3x"></i><div class="info"><h4>Payé — {{ $moisCourant }}</h4><p><b>{{ $money($totalMois) }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small warning coloured-icon"><i class="icon fa fa-line-chart fa-3x"></i><div class="info"><h4>Payé cette année</h4><p><b>{{ $money($totalAnnee) }}</b></p></div></div></div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-id-card"></i> Bénéficiaires &amp; salaire de base</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Bénéficiaire</th><th>Catégorie</th><th style="width:220px">Salaire de base</th><th>Salaire défini</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($beneficiaires as $beneficiaire)
                            @php($cle = $beneficiaire['type'].':'.$beneficiaire['id'])
                            @php($salaire = $salaires[$cle] ?? null)
                            <tr>
                                <td><strong>{{ $beneficiaire['nom'] }}</strong></td>
                                <td>{{ $beneficiaire['categorie'] }}</td>
                                <td>
                                    <form class="form-inline" method="POST" action="{{ route('admin.paies.salaires.store') }}">
                                        @csrf
                                        <input type="hidden" name="beneficiaire_type" value="{{ $beneficiaire['type'] }}">
                                        <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire['id'] }}">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="number" step="0.01" min="0" name="salaire_base" value="{{ old('salaire_base', $salaire?->salaire_base) }}" required>
                                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i></button></div>
                                        </div>
                                    </form>
                                </td>
                                <td>@if ($salaire)<span class="badge badge-success">{{ $money($salaire->salaire_base) }}</span>@else<span class="badge badge-secondary">Non défini</span>@endif</td>
                                <td class="text-nowrap">
                                    @if ($salaire)
                                        <button class="btn btn-sm btn-info js-payer" type="button" data-salaire="{{ $salaire->id }}" data-nom="{{ $beneficiaire['nom'] }}" data-base="{{ (float) $salaire->salaire_base }}"><i class="fa fa-money"></i> Payer</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucun professeur ni personnel actif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-credit-card"></i> Payer un salaire</h4>
                    @if ($salaires->isEmpty())
                        <p class="text-muted mb-0">Définissez d’abord un salaire de base ci-dessus.</p>
                    @else
                        <form method="POST" action="{{ route('admin.paies.store') }}">
                            @csrf
                            <div class="form-group"><label class="form-label">Bénéficiaire *</label>
                                <select class="form-control" name="salaire_id" id="salaireSelect" required>
                                    <option value="">Choisir</option>
                                    @foreach ($salaires as $salaire)
                                        <option value="{{ $salaire->id }}" data-base="{{ (float) $salaire->salaire_base }}" @selected(old('salaire_id') == $salaire->id)>{{ $salaire->nomBeneficiaire() }} — {{ $money($salaire->salaire_base) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group"><label class="form-label">Mois à payer *</label><input class="form-control" type="month" name="periode" value="{{ old('periode', $moisCourant) }}" required></div>
                                <div class="col-md-6 form-group"><label class="form-label">Date de paiement *</label><input class="form-control" type="text" data-date-fr name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}" required></div>
                                <div class="col-md-6 form-group"><label class="form-label">Primes</label><input class="form-control" type="number" step="0.01" min="0" name="primes" id="primes" value="{{ old('primes', 0) }}"></div>
                                <div class="col-md-6 form-group"><label class="form-label">Retenues</label><input class="form-control" type="number" step="0.01" min="0" name="retenues" id="retenues" value="{{ old('retenues', 0) }}"></div>
                                <div class="col-md-6 form-group"><label class="form-label">Mode *</label><select class="form-control" name="mode_paiement" required>@foreach ($modes as $valeur => $libelle)<option value="{{ $valeur }}" @selected(old('mode_paiement', 'especes') === $valeur)>{{ $libelle }}</option>@endforeach</select></div>
                                <div class="col-md-6 form-group"><label class="form-label">Référence</label><input class="form-control" name="reference" value="{{ old('reference') }}" maxlength="100"></div>
                                <div class="col-md-12 form-group"><label class="form-label">Observation</label><input class="form-control" name="observation" value="{{ old('observation') }}" maxlength="255"></div>
                            </div>
                            <div class="alert alert-info py-2" id="netInfo">Net à payer : <strong>—</strong></div>
                            <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-check"></i> Enregistrer le paiement du salaire</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="col-md-7">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-list"></i> Paiements de salaire ({{ $paies->count() }})</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead><tr><th>N° paie</th><th>Bénéficiaire</th><th>Mois</th><th class="text-right">Base</th><th class="text-right">Primes</th><th class="text-right">Retenues</th><th class="text-right">Net</th><th>Payé le</th>@if ($estAdmin)<th></th>@endif</tr></thead>
                            <tbody>
                                @forelse ($paies as $paie)
                                    <tr>
                                        <td><strong>{{ $paie->num_paie }}</strong></td>
                                        <td>{{ $paie->salaire?->nomBeneficiaire() ?? '—' }}</td>
                                        <td>{{ $paie->periode }}</td>
                                        <td class="text-right">{{ $money($paie->montant_base) }}</td>
                                        <td class="text-right">{{ $money($paie->primes) }}</td>
                                        <td class="text-right">{{ $money($paie->retenues) }}</td>
                                        <td class="text-right"><strong>{{ $money($paie->montant_net) }}</strong></td>
                                        <td>{{ $paie->date_paiement?->format('d/m/Y') }}</td>
                                        @if ($estAdmin)
                                            <td>
                                                <form method="POST" action="{{ route('admin.paies.destroy', $paie) }}" onsubmit="return confirm('Supprimer ce paiement de salaire ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $estAdmin ? 9 : 8 }}" class="text-center text-muted">Aucun paiement de salaire enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        (function () {
            var select = document.getElementById('salaireSelect');
            var primes = document.getElementById('primes');
            var retenues = document.getElementById('retenues');
            var info = document.getElementById('netInfo');

            function baseCourante() {
                if (!select || !select.value) { return 0; }
                var option = select.options[select.selectedIndex];
                return Number(option ? option.getAttribute('data-base') : 0) || 0;
            }

            function majNet() {
                if (!info) { return; }
                var net = Math.max(baseCourante() + (Number(primes?.value) || 0) - (Number(retenues?.value) || 0), 0);
                info.innerHTML = 'Salaire de base : <b>' + baseCourante().toFixed(2) + '</b> · Net à payer : <strong>' + net.toFixed(2) + '</strong>';
            }

            if (select) { select.addEventListener('change', majNet); }
            if (primes) { primes.addEventListener('input', majNet); }
            if (retenues) { retenues.addEventListener('input', majNet); }
            majNet();

            Array.prototype.forEach.call(document.querySelectorAll('.js-payer'), function (bouton) {
                bouton.addEventListener('click', function () {
                    if (select) { select.value = bouton.getAttribute('data-salaire'); }
                    majNet();
                    window.scrollTo({ top: document.getElementById('netInfo').offsetTop - 200, behavior: 'smooth' });
                });
            });
        })();
    </script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
