<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Délibération | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">
    <style>
        :root { --bg:#0b0b0c; --card:#242526; --div:#2f3031; --txt:#e4e6eb; --mut:#b0b3b8; --blue:#0866ff; }
        body { background:var(--bg); color:var(--txt); margin:0; font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif; }
        .entete { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 20px;
            background:var(--card); border-bottom:1px solid var(--div); position:sticky; top:0; z-index:9; }
        .entete h1 { margin:0; font-size:1.15rem; color:#fff; }
        .entete .sous { color:var(--mut); font-size:.78rem; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:999px; border:none;
            font-size:.82rem; font-weight:700; cursor:pointer; color:#fff; background:var(--blue); text-decoration:none; }
        .btn--neutre { background:#3a3b3c; color:var(--txt); }
        .contenu { max-width:1200px; margin:0 auto; padding:16px; }
        .carte { background:var(--card); border:1px solid var(--div); border-radius:12px; padding:16px; margin-bottom:14px; }
        .carte h2 { margin:0 0 12px; font-size:1rem; color:#fff; }
        .grille { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; }
        label { display:block; font-size:.76rem; color:var(--mut); margin-bottom:5px; }
        input[type=number], select { width:100%; background:var(--bg); border:1px solid var(--div); color:#fff;
            border-radius:8px; padding:8px 10px; font-size:.88rem; }
        table { width:100%; border-collapse:collapse; font-size:.86rem; }
        th, td { padding:9px 10px; border-bottom:1px solid var(--div); text-align:left; }
        th { color:var(--mut); font-size:.74rem; text-transform:uppercase; letter-spacing:.4px; }
        td.nom { color:#fff; font-weight:600; }
        .pill { font-size:.72rem; font-weight:700; padding:2px 9px; border-radius:999px; }
        .pill--admis { background:#14532d; color:#dcfce7; }
        .pill--ajourne { background:#78350f; color:#fef3c7; }
        .pill--redouble { background:#7f1d1d; color:#fee2e2; }
        .alerte { padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:.85rem; }
        .alerte--ok { background:#14532d; color:#dcfce7; }
        .alerte--ko { background:#7f1d1d; color:#fee2e2; }
        .rangee { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .vide { color:var(--mut); padding:18px; text-align:center; }
        .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; }
        .stat { display:flex; flex-direction:column; gap:3px; background:var(--bg); border:1px solid var(--div);
            border-left:4px solid var(--blue); border-radius:12px; padding:14px; }
        .stat--admis { border-left-color:#22c55e; }
        .stat--ajourne { border-left-color:#f59e0b; }
        .stat--redouble { border-left-color:#ef4444; }
        .stat__v { font-size:1.7rem; font-weight:800; color:#fff; line-height:1; }
        .stat__k { font-size:.78rem; color:var(--mut); }
        .stat__s { font-size:.78rem; color:#fff; font-weight:600; }
    </style>
</head>
<body>

<header class="entete">
    <div>
        <h1><i class="fa-solid fa-gavel"></i> Délibération de fin d'année</h1>
        <div class="sous">
            {{ $annee ? $annee->libelle : 'Aucune année en cours' }}
            @if (! empty($regles)) · seuils : {{ $regles['seuil_admission'] }} % / repêchage {{ $regles['seuil_repechage'] }} % @endif
        </div>
    </div>
    <a class="btn btn--neutre" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Tableau de bord</a>
</header>

<main class="contenu">
    @if (session('success'))<div class="alerte alerte--ok">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alerte alerte--ko">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alerte alerte--ko"><ul style="margin:0;padding-left:18px">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul></div>
    @endif
    @unless ($annee)
        <div class="alerte alerte--ko">Aucune année scolaire n'est en cours : ouvrez une année avant de délibérer.</div>
    @endunless

    {{-- Critères --}}
    <section class="carte">
        <h2><i class="fa-solid fa-sliders"></i> Critères de délibération</h2>
        <form method="POST" action="{{ route('admin.deliberations.criteres') }}">
            @csrf @method('PUT')
            <div class="grille">
                <div>
                    <label>Seuil d'admission (%) — moyenne générale</label>
                    <input type="number" step="0.01" min="0" max="100" name="deliberation_seuil_admission"
                           value="{{ old('deliberation_seuil_admission', $regles['seuil_admission']) }}" required>
                </div>
                <div>
                    <label>Seuil de repêchage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="deliberation_seuil_repechage"
                           value="{{ old('deliberation_seuil_repechage', $regles['seuil_repechage']) }}" required>
                </div>
                <div>
                    <label>Nb max de cours en échec (vide = illimité)</label>
                    <input type="number" min="0" max="50" name="deliberation_max_cours_echoues"
                           value="{{ old('deliberation_max_cours_echoues', $regles['max_cours_echoues']) }}">
                </div>
                <div>
                    <label>Note minimale par cours (%) (indicatif)</label>
                    <input type="number" step="0.01" min="0" max="100" name="deliberation_note_min_cours"
                           value="{{ old('deliberation_note_min_cours', $regles['note_min_cours']) }}">
                </div>
            </div>
            <div class="rangee" style="margin-top:12px">
                <label style="margin:0"><input type="checkbox" name="deliberation_active" value="1"
                    @checked($parametres->deliberation_active)> Module de délibération actif</label>
                <button class="btn" type="submit"><i class="fa-solid fa-save"></i> Enregistrer les critères</button>
            </div>
        </form>
    </section>

    {{-- Synthèse de l'effectif délibéré --}}
    @if ($classe && $lignes->isNotEmpty())
        <section class="carte">
            <h2><i class="fa-solid fa-chart-pie"></i> Synthèse — {{ $classe->nom }}</h2>
            <div class="stats">
                <div class="stat">
                    <span class="stat__v">{{ $synthese['total'] }}</span>
                    <span class="stat__k">Élèves délibérés</span>
                    <span class="stat__s">{{ $synthese['garcons'] }} G · {{ $synthese['filles'] }} F</span>
                </div>
                @foreach (['admis' => 'Admis (passent)', 'ajourne' => 'Ajournés (repêchage)', 'redouble' => 'Redoublants'] as $cle => $libelle)
                    <div class="stat stat--{{ $cle }}">
                        <span class="stat__v">{{ $synthese['decisions'][$cle]['total'] }}</span>
                        <span class="stat__k">{{ $libelle }}</span>
                        <span class="stat__s">{{ $synthese['decisions'][$cle]['g'] }} G · {{ $synthese['decisions'][$cle]['f'] }} F</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Effectif --}}
    <section class="carte">
        <h2><i class="fa-solid fa-users"></i> Effectif à délibérer</h2>
        <form method="GET" action="{{ route('admin.deliberations.index') }}" class="rangee">
            <select name="classe" onchange="this.form.submit()" style="max-width:320px">
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected($classe && $classe->id === $c->id)>
                        {{ $c->nom }}@if ($c->ordre !== null) · niveau {{ $c->ordre }}@endif
                    </option>
                @endforeach
            </select>
            <span style="color:var(--mut);font-size:.8rem">
                @if ($classeSuivante)
                    Passage vers : <strong style="color:#fff">{{ $classeSuivante->nom }}</strong>
                @else
                    <em>Aucune classe de niveau supérieur définie pour cette classe.</em>
                @endif
            </span>
        </form>
    </section>

    @if ($classe && $lignes->isNotEmpty())
        <form method="POST" action="{{ route('admin.deliberations.enregistrer') }}">
            @csrf
            <input type="hidden" name="annee_scolaire_id" value="{{ $annee->id }}">
            <section class="carte">
                <h2><i class="fa-solid fa-list-check"></i> Décisions — {{ $classe->nom }} ({{ $lignes->count() }} élève(s))</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Élève</th><th>Moyenne générale</th><th>Cours en échec</th>
                            <th>Proposition du jury</th><th>Décision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lignes as $ligne)
                            @php($proposee = $ligne['analyse']['decision_proposee'])
                            <tr>
                                <td class="nom">
                                    {{ $ligne['eleve']?->nom }} {{ $ligne['eleve']?->postnom }} {{ $ligne['eleve']?->prenom }}
                                    <div style="color:var(--mut);font-weight:400;font-size:.74rem">{{ $ligne['eleve']?->matricule }}</div>
                                </td>
                                <td>{{ number_format($ligne['analyse']['pourcentage'], 2, ',', ' ') }} %</td>
                                <td>{{ $ligne['analyse']['cours_echoues'] }}</td>
                                <td><span class="pill pill--{{ $proposee }}">{{ $decisions[$proposee] ?? $proposee }}</span></td>
                                <td>
                                    <select name="decisions[{{ $ligne['inscription']->id }}]">
                                        @foreach ($decisions as $valeur => $libelle)
                                            <option value="{{ $valeur }}" @selected($ligne['decision'] === $valeur)>{{ $libelle }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="rangee" style="margin-top:14px">
                    <button class="btn" type="submit"><i class="fa-solid fa-check"></i> Enregistrer les décisions</button>
                </div>
            </section>
        </form>
    @elseif ($classe)
        <div class="carte vide">Aucun élève actif dans cette classe pour l'année en cours.</div>
    @endif

    {{-- Application en masse --}}
    @if ($annee)
        <form method="POST" action="{{ route('admin.deliberations.appliquer') }}">
            @csrf
            <input type="hidden" name="annee_scolaire_id" value="{{ $annee->id }}">
            <section class="carte">
                <h2><i class="fa-solid fa-arrow-up-right-dots"></i> Appliquer le passage (en masse)</h2>
                <p style="color:var(--mut);font-size:.82rem;margin-top:0">
                    Les <strong>admis</strong> sont inscrits dans la classe de niveau supérieur,
                    les <strong>ajournés</strong> et <strong>redoublants</strong> restent dans la même classe.
                    Les inscriptions sont créées « en attente » (à valider à l'encaissement).
                </p>
                <div class="rangee">
                    <div style="min-width:260px">
                        <label>Année scolaire cible @if ($anneeSuivante) — l'année suivante est {{ $anneeSuivante->libelle }} @endif</label>
                        <select name="annee_cible_id" required>
                            <option value="">— choisir —</option>
                            @foreach ($annees as $a)
                                @continue($a->id === $annee->id)
                                <option value="{{ $a->id }}" @selected((int) old('annee_cible_id', $anneeSuivante?->id) === $a->id)>
                                    {{ $a->libelle }}@if ($anneeSuivante && $a->id === $anneeSuivante->id) · année suivante @endif
                                </option>
                            @endforeach
                        </select>
                        @if (! $anneeSuivante)
                            <small style="color:#fca5a5">Aucune année postérieure n'existe : créez l'année suivante avant d'appliquer le passage.</small>
                        @endif
                    </div>
                    <button class="btn" type="submit"><i class="fa-solid fa-rocket"></i> Appliquer les décisions enregistrées</button>
                </div>
            </section>
        </form>
    @endif
</main>
</body>
</html>
