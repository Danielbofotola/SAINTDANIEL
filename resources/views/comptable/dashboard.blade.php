<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace comptable | {{ $systemSettings->nom_court }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
     <!-- Icons FontAwesome & Themify -->
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">

    <!-- DataTables Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <!-- Able Pro / Main Styling -->
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">

    <!-- Chart.js -->
    <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>
    <style>
        :root {
            --bg-canvas: #d0dbe5;
            --card-bg: #f2f5f8;
            --card-border: #b0c2d0;
            --green-btn: #107c41;
            --green-hover: #0b582e;
            --red-btn: #a80000;
            --accent-gold: #c19a32;
            --bottom-nav-height: 70px;
        }

        body {
            background-color: var(--bg-canvas);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            font-size: 0.82rem;
            padding-bottom: calc(var(--bottom-nav-height) + 15px);
        }

        .caisse-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            height: 100%;
        }

        .card-title-header {
            font-size: 0.95rem;
            font-weight: 700;
            text-align: center;
            color: #0f172a;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .inner-block {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
        }

        /* Jauge CSS */
        .gauge-container {
            position: relative;
            width: 85px;
            height: 42px;
            overflow: hidden;
            margin: 0 auto;
        }
        .gauge-body {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: conic-gradient(from 270deg, #107c41 0% 135deg, #e2e8f0 135deg 270deg);
        }
        .gauge-inner {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 65px;
            height: 65px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 6px;
            font-weight: 800;
            font-size: 0.85rem;
            color: #0f172a;
        }

        .form-select-caisse, .form-control-caisse {
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid #94a3b8;
            border-radius: 5px;
            padding: 6px 10px;
        }

        .btn-encaisser-main {
            background-color: var(--green-btn);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            border-radius: 5px;
            border: none;
            padding: 8px 12px;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-encaisser-main:hover {
            background-color: var(--green-hover);
            color: white;
        }

        .btn-valider-sm {
            background-color: var(--green-btn);
            color: white;
            font-weight: 700;
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 4px;
            border: none;
        }

        .btn-annuler-sm {
            background-color: var(--red-btn);
            color: white;
            font-weight: 700;
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 4px;
            border: none;
        }

        .sub-label-bar {
            text-align: center;
            font-size: 0.68rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .table-caisse {
            font-size: 0.78rem;
            background: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 0;
        }
        .table-caisse th {
            background-color: #cbd5e1;
            color: #0f172a;
            font-weight: 700;
        }

        /* Styles pour la barre du bas (Navbar Bottom) */
        .bottom-actions-bar {
            background-color: #0f172a;
            border-top: 2px solid #334155;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
            min-height: var(--bottom-nav-height);
        }

        .action-box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 6px 4px;
            text-align: center;
            cursor: pointer;
            color: #f8fafc;
            transition: all 0.2s ease-in-out;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .action-box:hover {
            background: #334155;
            border-color: #38bdf8;
            color: #38bdf8;
        }
        .action-box i {
            font-size: 1.1rem;
            display: block;
            margin-bottom: 2px;
        }
        .action-box span {
            font-size: 0.7rem;
            font-weight: 600;
            display: block;
            white-space: nowrap;
        }

        /* Calculatrice */
        .calc-display-box {
            background-color: #e2e8f0;
            border: 1px solid #94a3b8;
            border-radius: 5px;
            padding: 6px 10px;
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        .calc-btn-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }
        .calc-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 0;
            font-weight: 700;
        }
        .calc-btn.gold {
            background-color: var(--accent-gold);
            color: white;
            border: none;
        }
        .calc-btn.equal {
            background-color: #0284c7;
            color: white;
            border: none;
        }

        /* Impresion spécifique au Bordereau */
        @media print {
            body * {
                visibility: hidden;
            }
            .printable-receipt, .printable-receipt * {
                visibility: visible;
            }
            .printable-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    
        a.action-box, button.action-box { text-decoration: none; font-family: inherit; }
        .progress-thin { height: 6px; min-width: 70px; background: #e2e8f0; }
        .progress-thin .progress-bar { background: var(--green-btn); }
</style>
</head>
<body class="p-2">
    @php
        $displayName = trim(($user->prenom ?: '').' '.($user->nom ?: '')) ?: $user->username;
        $money = fn ($value) => number_format((float) $value, 0, ',', ' ');
        $angle = min(max((float) $taux, 0), 100) * 1.8;
        $dernier = $derniersPaiements->first();
    @endphp
    @include('partials.impersonation-banner')
    <div class="d-flex justify-content-end mb-2">@include('partials.raccourcis', ['raccourcis' => \App\Support\Raccourcis::pour($user), 'variante' => 'clair'])</div>

    <div class="container-fluid">
        <div class="row g-2">

            <!-- PRINCIPAL -->
            <div class="col-lg-9">

                @if (! $schoolYear)
                    <div class="alert alert-info py-2 mb-2" role="status"><strong>Aucune année scolaire en cours n’est définie.</strong> Les frais et paiements s’afficheront dès que l’administration en activera une.</div>
                @endif

                <div class="row g-2 mb-2">
                    <!-- Tableau de bord de caisse -->
                    <div class="col-md-7">
                        <div class="caisse-card">
                            <div class="card-title-header">Tableau de bord de caisse : {{ $displayName }}</div>
                            <div class="inner-block d-flex align-items-center mb-2">
                                @if ($user->photo)
                                    <img src="{{ asset('storage/'.$user->photo) }}" alt="" class="rounded-circle me-2" style="width:44px;height:44px;object-fit:cover;">
                                @else
                                    <span class="rounded-circle me-2 d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width:44px;height:44px;background:#107c41;">{{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}</span>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ trim($user->nom.' '.$user->postnom.' '.$user->prenom) ?: $user->username }}</div>
                                    <div class="text-muted">{{ '@'.$user->username }} · Comptable (caisse)</div>
                                    @if ($user->email)<div class="text-muted">{{ $user->email }}@if ($user->telephone) · {{ $user->telephone }}@endif</div>@endif
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>En ligne</span>
                                    <div class="text-muted mt-1">Connexion : {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d/m/Y H:i') : '—' }}</div>
                                     <a href="{{ route('comptable.profil') }}" class="btn btn-sm btn-outline-success mt-1"><i class="bi bi-person-circle me-1"></i>Mon profil</a>
                                </div>
                            </div>
                            <div class="text-center text-secondary mb-2">{{ $schoolYear ? 'Année scolaire '.$schoolYear->libelle : 'Année scolaire non définie' }}</div>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-7">
                                    <div class="inner-block text-center">
                                        <div class="text-muted fw-semibold">Total encaissé</div>
                                        <div class="fw-bold fs-3 my-1">
                                            @forelse ($encaisseParDevise as $devise => $montant){{ $money($montant, $devise) }}@if (! $loop->last) · @endif @empty {{ $money($encaisse) }} @endforelse
                                        </div>
                                        <div class="text-secondary">sur {{ $money($fraisDefinis) }} de frais définis</div>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="inner-block text-center">
                                        <div class="gauge-container" role="img" aria-label="Taux de recouvrement : {{ $taux }} %">
                                            <div class="gauge-body" style="background: conic-gradient(from 270deg, #107c41 0deg {{ $angle }}deg, #e2e8f0 {{ $angle }}deg 180deg);"></div>
                                            <div class="gauge-inner">{{ $taux }} %</div>
                                        </div>
                                        <div class="text-secondary fw-semibold mt-1">Recouvrement</div>
                                    </div>
                                </div>
                            </div>
                            <div class="inner-block text-center py-2">
                                <span class="fw-bold">Reste à percevoir :</span> {{ $money($reste) }}
                            </div>
                             <div class="mt-2">
                                 <a href="{{ route('comptable.paiements.index') }}" class="btn btn-success fw-bold w-100"><i class="bi bi-cash-coin me-1"></i> Encaisser un paiement</a>
                             </div>
                              <div class="mt-1">
                                  <a href="{{ route('comptable.recouvrement.index') }}" class="btn btn-outline-primary bg-white fw-bold w-100"><i class="bi bi-graph-up-arrow me-1"></i> Recouvrement &amp; évolution</a>
                              </div>
                              <div class="mt-1">
                                  <a href="{{ route('comptable.depenses.index') }}" class="btn btn-outline-danger bg-white fw-bold w-100"><i class="bi bi-cart-dash me-1"></i> Dépenses de la caisse</a>
                              </div>
                              <div class="mt-1">
                                  <a href="{{ route('comptable.paies.index') }}" class="btn btn-outline-success bg-white fw-bold w-100"><i class="bi bi-cash-stack me-1"></i> Paie &amp; salaires</a>
                              </div>
                        </div>
                    </div>

                    <!-- Synthèse -->
                    <div class="col-md-5">
                        <div class="caisse-card">
                            <div class="card-title-header">Synthèse de l’année</div>
                            <div class="bg-white border rounded p-2">
                                <div class="d-flex justify-content-between border-bottom pb-1 mb-1"><div><div class="fw-bold">Élèves inscrits</div><div class="text-muted">Inscriptions actives</div></div><div class="fw-bold fs-6">{{ $elevesInscrits }}</div></div>
                                <div class="d-flex justify-content-between border-bottom pb-1 mb-1"><div><div class="fw-bold">Paiements enregistrés</div><div class="text-muted">Reçus émis</div></div><div class="fw-bold fs-6">{{ $nbPaiements }}</div></div>
                                <div class="d-flex justify-content-between"><div><div class="fw-bold">Encaissé par élève</div><div class="text-muted">Moyenne perçue</div></div><div class="fw-bold fs-6">{{ $elevesInscrits > 0 ? $money($encaisse / $elevesInscrits) : 0 }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recouvrement par classe -->
                <div class="caisse-card mb-2" id="recouvrement">
                    <div class="card-title-header">Recouvrement par classe</div>
                    @if ($parClasse->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-caisse align-middle">
                                <thead><tr><th>Classe</th><th class="text-end">Défini</th><th class="text-end">Encaissé</th><th class="text-end">Reste</th><th>Avancement</th></tr></thead>
                                <tbody>
                                    @foreach ($parClasse as $ligne)
                                        @php($pct = $ligne->defini > 0 ? min(100, round($ligne->encaisse / $ligne->defini * 100)) : 0)
                                        <tr>
                                            <td class="fw-bold">{{ $ligne->nom }}</td>
                                            <td class="text-end">{{ $money($ligne->defini) }}</td>
                                            <td class="text-end">{{ $money($ligne->encaisse) }}</td>
                                            <td class="text-end fw-bold">{{ $money(max($ligne->defini - $ligne->encaisse, 0)) }}</td>
                                            <td><div class="d-flex align-items-center"><div class="progress progress-thin flex-grow-1 me-2"><div class="progress-bar" style="width: {{ $pct }}%"></div></div><span>{{ $pct }} %</span></div></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="inner-block text-center text-muted py-4">Aucun frais défini pour l’année.</div>
                    @endif
                </div>

                <!-- Historique des reçus -->
                <div class="caisse-card d-none" id="historique">
                    <div class="card-title-header">Historique récent des reçus</div>
                    @if ($derniersPaiements->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-caisse align-middle">
                                <thead><tr><th>N° Reçu</th><th>Élève</th><th>Classe</th><th class="text-end">Montant</th><th>Date/Heure</th></tr></thead>
                                <tbody>
                                    @foreach ($derniersPaiements as $paiement)
                                        <tr>
                                            <td class="fw-bold">{{ $paiement->num_recu }}</td>
                                            <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}</td>
                                            <td>{{ $paiement->inscription?->classe?->nom ?? 'Classe inconnue' }}</td>
                                            <td class="text-end fw-bold">{{ $money($paiement->montant_paye) }}</td>
                                            <td>{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="inner-block text-center text-muted py-4">Aucun paiement enregistré pour l’année en cours.</div>
                    @endif
                </div>
            </div>

            <!-- LATERAL -->
            <div class="col-lg-3">
                <div class="d-flex flex-column gap-2">

                    <!-- Bordereau : dernier paiement -->
                    <div class="card shadow-sm border-primary printable-receipt">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                            <h5 class="mb-0 text-uppercase" style="font-size: 0.85rem;"><i class="bi bi-receipt me-2"></i>Bordereau</h5>
                            @if ($dernier)<span class="badge bg-light text-primary fw-bold" style="font-size: 0.65rem;">{{ $dernier->num_recu }}</span>@endif
                        </div>
                        <div class="card-body p-2">
                            @if ($dernier)
                                <div class="mb-2 pb-2 border-bottom">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Élève :</small>
                                    <span class="fw-bold">{{ $dernier->inscription?->eleve?->nom }} {{ $dernier->inscription?->eleve?->prenom }}</span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Classe :</small>
                                    <span class="fw-semibold">{{ $dernier->inscription?->classe?->nom ?? 'Classe inconnue' }}</span>
                                </div>
                                <div class="bg-light p-2 rounded border mb-2">
                                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Date :</span><span class="fw-semibold">{{ $dernier->date_paiement?->format('d/m/Y H:i') }}</span></div>
                                    <hr class="my-1 border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center pt-1"><span class="fw-bold text-uppercase">Total :</span><span class="fw-bold text-success" style="font-size: 0.95rem;">{{ $money($dernier->montant_paye) }}</span></div>
                                </div>
                                <div class="row pt-1 text-center" style="font-size: 0.65rem;">
                                    <div class="col-6"><p class="mb-3 text-muted">Signature tuteur</p><div class="border-bottom mx-1"></div></div>
                                    <div class="col-6"><p class="mb-3 text-muted">Cachet caisse</p><div class="border-bottom mx-1"></div></div>
                                </div>
                            @else
                                <div class="text-center text-muted py-3">Aucun reçu à afficher.</div>
                            @endif
                        </div>
                        @if ($dernier)
                            <div class="card-footer bg-white border-top-0 text-end py-2 no-print">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();"><i class="bi bi-printer me-1"></i>Imprimer le reçu</button>
                            </div>
                        @endif
                    </div>

                    <!-- Calculatrice -->
                    <div class="caisse-card no-print">
                        <div class="card-title-header mb-1">Calculatrice</div>
                        <div class="calc-display-box fs-5 mb-2" id="calcDisplay">0</div>
                        <div class="row g-1">
                            <div class="col-9">
                                <div class="calc-btn-grid">
                                    @foreach (['7','8','9','÷','4','5','6','×','1','2','3','-'] as $k)
                                        <button type="button" class="calc-btn" data-k="{{ $k }}">{{ $k }}</button>
                                    @endforeach
                                    <button type="button" class="calc-btn" style="grid-column: span 2;" data-k="0">0</button>
                                    <button type="button" class="calc-btn" data-k=".">.</button>
                                    <button type="button" class="calc-btn" data-k="+">+</button>
                                </div>
                            </div>
                            <div class="col-3 d-flex flex-column gap-1">
                                <button type="button" class="calc-btn gold w-100" data-k="back" aria-label="Effacer"><i class="bi bi-backspace"></i></button>
                                <button type="button" class="calc-btn equal w-100 h-100" data-k="=">=</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BARRE DU BAS -->
    <nav class="navbar fixed-bottom bottom-actions-bar px-2 py-1 no-print" aria-label="Navigation rapide">
        <div class="container-fluid p-0">
            <div class="row g-1 w-100 align-items-center m-0">
                <div class="col-auto me-2 text-white fw-bold text-uppercase d-none d-md-block" style="font-size: 0.75rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ $systemSettings->nom_court }} · Comptable
                </div>
                <div class="col">
                    <div class="row g-1">
                        <div class="col"><a class="action-box" href="{{ route('comptable.dashboard') }}"><i class="bi bi-speedometer2 text-success"></i><span>Vue générale</span></a></div>
                        <div class="col"><a class="action-box" href="#recouvrement"><i class="bi bi-bar-chart-line text-primary"></i><span>Recouvrement</span></a></div>
                        <div class="col"><a class="action-box" href="#historique"><i class="bi bi-receipt text-warning"></i><span>Reçus</span></a></div>
                        <div class="col"><a class="action-box" href="{{ route('profile.show') }}"><i class="bi bi-person text-info"></i><span>Mon profil</span></a></div>
                        <div class="col">
                            <form method="POST" action="{{ route('logout') }}" class="h-100">@csrf
                                <button type="submit" class="action-box w-100"><i class="bi bi-box-arrow-right text-danger"></i><span>Déconnexion</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const display = document.getElementById('calcDisplay');
            let expr = '';
            document.querySelectorAll('[data-k]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const k = btn.dataset.k;
                    if (expr === 'Erreur') expr = '';
                    if (k === 'back') expr = expr.slice(0, -1);
                    else if (k === '=') {
                        try { expr = String(Function('"use strict"; return (' + expr.replace(/×/g, '*').replace(/÷/g, '/') + ')')()); }
                        catch (e) { expr = 'Erreur'; }
                    } else expr += k;
                    display.textContent = expr || '0';
                });
            });
        })();
    </script>
@include('partials.cloche-flottante')
</body>
</html>