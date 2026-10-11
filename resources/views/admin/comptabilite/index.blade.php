<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comptabilité OHADA</title>
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
                <h1><i class="fa fa-calculator"></i> Comptabilité OHADA (SYSCOHADA)</h1>
                <p>Plan comptable, écritures en partie double et balance générale.</p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.comptabilite.plan') }}"><i class="fa fa-sitemap"></i> Plan comptable</a>
                <a class="btn btn-dark" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                <a class="btn btn-info" href="{{ route('admin.comptabilite.balance') }}"><i class="fa fa-balance-scale"></i> Balance générale</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-book fa-3x"></i><div class="info"><h4>Écritures</h4><p><b>{{ $totaux['ecritures'] }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-arrow-up fa-3x"></i><div class="info"><h4>Total débit</h4><p><b>{{ $money($totaux['debit']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-arrow-down fa-3x"></i><div class="info"><h4>Total crédit</h4><p><b>{{ $money($totaux['credit']) }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small danger coloured-icon"><i class="icon fa fa-check fa-3x"></i><div class="info"><h4>Équilibre</h4><p><b>{{ abs($totaux['debit'] - $totaux['credit']) < 0.005 ? 'OK' : 'Déséquilibré' }}</b></p></div></div></div>
        </div>

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-refresh"></i> Synchroniser les opérations du système</h4>
            <p class="text-muted">Paiements d’élèves (→ 706), dépenses (→ charges 6xx) et salaires versés (→ 661) sont transformés en écritures. Une opération déjà comptabilisée n’est jamais dupliquée.</p>
            <p>
                À comptabiliser :
                <span class="badge badge-primary">{{ $aComptabiliser['paiements'] }} paiement(s)</span>
                <span class="badge badge-warning">{{ $aComptabiliser['depenses'] }} dépense(s)</span>
                <span class="badge badge-info">{{ $aComptabiliser['paies'] }} salaire(s)</span>
            </p>
            <form method="POST" action="{{ route('admin.comptabilite.synchroniser') }}">
                @csrf
                <button class="btn btn-primary" type="submit"><i class="fa fa-refresh"></i> Synchroniser maintenant</button>
            </form>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-list"></i> Dernières écritures</h4>
                    @forelse ($ecritures as $ecriture)
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between">
                                <span>
                                    <strong>{{ $ecriture->libelle }}</strong>
                                    <small class="text-muted">— {{ $ecriture->date_ecriture?->format('d/m/Y') }} · journal {{ $ecriture->journal }}
                                        @if ($ecriture->reference) · réf. {{ $ecriture->reference }}@endif
                                        @if ($ecriture->source_type) · <em>auto ({{ $ecriture->source_type }})</em>@endif
                                    </small>
                                </span>
                                <span class="text-nowrap">
                                    <span class="badge badge-{{ $ecriture->estEquilibree() ? 'success' : 'danger' }}">{{ $money($ecriture->total_debit) }}</span>
                                    <form class="d-inline" method="POST" action="{{ route('admin.comptabilite.ecritures.destroy', $ecriture) }}" onsubmit="return confirm('Supprimer cette écriture ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i></button>
                                    </form>
                                </span>
                            </div>
                            <table class="table table-sm table-bordered mb-0 mt-1">
                                <thead><tr><th>Compte</th><th>Libellé</th><th class="text-right">Débit</th><th class="text-right">Crédit</th></tr></thead>
                                <tbody>
                                    @foreach ($ecriture->lignes as $ligne)
                                        <tr>
                                            <td>{{ $ligne->compte?->numero }} — {{ $ligne->compte?->libelle }}</td>
                                            <td>{{ $ligne->libelle }}</td>
                                            <td class="text-right">{{ (float) $ligne->debit > 0 ? $money($ligne->debit) : '' }}</td>
                                            <td class="text-right">{{ (float) $ligne->credit > 0 ? $money($ligne->credit) : '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune écriture enregistrée. Utilisez « Synchroniser » ou saisissez une écriture manuelle.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-md-5">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-pencil"></i> Saisir une écriture manuelle</h4>
                    <form method="POST" action="{{ route('admin.comptabilite.ecritures.store') }}" id="formEcriture">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group"><label class="form-label">Date *</label><input class="form-control" type="text" data-date-fr name="date_ecriture" value="{{ old('date_ecriture', now()->toDateString()) }}" required></div>
                            <div class="col-md-6 form-group"><label class="form-label">Journal *</label><input class="form-control" name="journal" value="{{ old('journal', 'OD') }}" maxlength="10" required></div>
                            <div class="col-md-8 form-group"><label class="form-label">Libellé *</label><input class="form-control" name="libelle" value="{{ old('libelle') }}" maxlength="200" required></div>
                            <div class="col-md-4 form-group"><label class="form-label">Référence</label><input class="form-control" name="reference" value="{{ old('reference') }}" maxlength="100"></div>
                        </div>
                        <table class="table table-sm table-bordered" id="tableLignes">
                            <thead><tr><th>Compte</th><th style="width:110px">Débit</th><th style="width:110px">Crédit</th><th></th></tr></thead>
                            <tbody></tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <button class="btn btn-sm btn-outline-primary" type="button" id="ajouterLigne"><i class="fa fa-plus"></i> Ajouter une ligne</button>
                            <span class="text-muted">Débit <strong id="totalDebit">0.00</strong> · Crédit <strong id="totalCredit">0.00</strong></span>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Enregistrer l’écriture</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        (function () {
            var comptes = @json($comptes->map(fn ($compte) => ['id' => $compte->id, 'label' => $compte->numero.' — '.$compte->libelle])->values());
            var tbody = document.querySelector('#tableLignes tbody');
            var totalDebit = document.getElementById('totalDebit');
            var totalCredit = document.getElementById('totalCredit');

            function options() {
                return '<option value="">Choisir un compte</option>' + comptes.map(function (c) {
                    return '<option value="' + c.id + '">' + c.label + '</option>';
                }).join('');
            }

            function majTotaux() {
                var debit = 0, credit = 0;
                Array.prototype.forEach.call(tbody.querySelectorAll('tr'), function (tr) {
                    debit += Number(tr.querySelector('.debit').value) || 0;
                    credit += Number(tr.querySelector('.credit').value) || 0;
                });
                totalDebit.textContent = debit.toFixed(2);
                totalCredit.textContent = credit.toFixed(2);
            }

            function ajouterLigne() {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><select class="form-control form-control-sm" name="lignes[][compte_id]">' + options() + '</select></td>'
                    + '<td><input class="form-control form-control-sm debit" type="number" step="0.01" min="0" name="lignes[][debit]"></td>'
                    + '<td><input class="form-control form-control-sm credit" type="number" step="0.01" min="0" name="lignes[][credit]"></td>'
                    + '<td><button class="btn btn-sm btn-danger js-supprimer" type="button"><i class="fa fa-times"></i></button></td>';
                tbody.appendChild(tr);
                tr.querySelector('.debit').addEventListener('input', majTotaux);
                tr.querySelector('.credit').addEventListener('input', majTotaux);
                tr.querySelector('.js-supprimer').addEventListener('click', function () {
                    tr.remove();
                    majTotaux();
                });
            }

            document.getElementById('ajouterLigne').addEventListener('click', ajouterLigne);
            ajouterLigne();
            ajouterLigne();
        })();
    </script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
