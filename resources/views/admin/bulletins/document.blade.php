<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin — {{ $inscription->eleve?->nom }} {{ $inscription->eleve?->prenom }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 10px; color: #000; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; border: 2px solid #000; padding: 10px; box-sizing: border-box; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .header-table td { text-align: center; vertical-align: middle; }
        .header-title { font-weight: bold; font-size: 13px; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .info-table td { border: 1px solid #000; padding: 2px 4px; font-size: 10px; }
        .id-grid { display: inline-block; border: 1px solid #000; width: 15px; height: 15px; text-align: center; line-height: 15px; margin-right: 1px; }
        .main-table { width: 100%; border-collapse: collapse; font-size: 10px; text-align: center; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 2px 3px; }
        .main-table th { background-color: #f2f2f2; font-weight: bold; }
        .section-header { background-color: #e0e0e0; font-weight: bold; text-align: center; text-transform: uppercase; }
        .text-left { text-align: left; padding-left: 5px !important; }
        .bg-black { background-color: #000; }
        .footer-section { margin-top: 10px; font-size: 10px; line-height: 1.4; }
        .footer-table { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .footer-table td { vertical-align: top; }
        .toolbar { max-width: 1000px; margin: 0 auto 10px; text-align: right; }
        .bas-page { width: 100%; border-collapse: collapse; margin-top: 8px; }
        #bulletin-qr { display: inline-block; }
        #bulletin-qr img, #bulletin-qr canvas { width: 84px; height: 84px; }
        @media print { .toolbar { display: none; } body { margin: 0; } .container { border-width: 1px; } }
    </style>
</head>
<body>
@php
    $fmt = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $eleve = $inscription->eleve;
    $classe = $inscription->classe;
    $matricule = (string) ($eleve?->matricule ?? '');
    $digits = preg_replace('/\D/', '', $matricule);

    // Éléments du bulletin réglés dans Paramètres (Administration → Profil → Paramètres).
    $bulletinPays = $systemSettings->bulletin_pays ?: 'République Démocratique du Congo';
    $bulletinMinistere = $systemSettings->bulletin_ministere ?: "Ministère de l'Enseignement Primaire, Secondaire et Professionnel";
    // Titre réel : cycle, section et option de la classe de l'élève
    // (redites écartées), sauf titre imposé dans les Paramètres.
    $structure = \App\Support\StructureClasse::libelle($classe);
    $bulletinTitre = $systemSettings->bulletin_titre ?: ('Bulletin annuel'.($structure !== '' ? ' — '.$structure : ''));
    $bulletinReference = $systemSettings->bulletin_reference ?: 'EBE/P.S./010';
    $bulletinSignataire = $systemSettings->bulletin_signataire ?: 'Le Chef d’Établissement';
    $bulletinLieuSignature = $systemSettings->bulletin_lieu_signature ?: '__________________________';
    $afficherPlace = $systemSettings->bulletin_afficher_place ?? true;
    $afficherConduite = $systemSettings->bulletin_afficher_conduite ?? true;
    $ouLigne = fn ($valeur) => filled($valeur) ? $valeur : '______________________';
    $qrBulletin = \App\Support\BulletinUnique::donnees($inscription);
@endphp

<div class="toolbar">
    @php($retour = auth()->user()?->role === 'prefetture' ? route('prefecture.bulletins') : route('admin.bulletins.index'))
    <a href="{{ $retour }}" style="margin-right:8px;">← Retour</a>
    <button onclick="window.print()">🖨️ Imprimer</button>
</div>

<div class="container">
    <table class="header-table">
        <tr>
            <td style="width: 15%;"><div style="font-weight:bold; color:blue; font-size:10px;">
            <img src="{{ asset('assets/images/drape.png') }}" width="100" alt="">    
            </div></td>
            <td style="width: 70%;">
                <div class="header-title">{{ $bulletinPays }}</div>
                <div class="header-title" style="font-size: 12px;">{{ $bulletinMinistere }}</div>
                <div style="font-size: 11px;">{{ $systemSettings->nom_etablissement ?? '' }}</div>
            </td>
            <td style="width: 15%;"><div style="font-weight:bold; color:red; font-size:10px;">
                  <img src="{{ asset('assets/images/622px-Armoiries_de_la_République_démocratique_du_Congo_-_2006.png') }}" width="100" alt="">
            </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td colspan="3">
                <b>N° ID :</b>
                @if ($digits !== '')
                    @foreach (str_split($digits) as $chiffre)<span class="id-grid">{{ $chiffre }}</span>@endforeach
                @else
                    @for ($i = 0; $i < 26; $i++)<span class="id-grid"></span>@endfor
                @endif
            </td>
        </tr>
        <tr>
            <td><b>PROVINCE :</b> {{ $ouLigne($systemSettings->bulletin_province) }}</td>
            <td rowspan="3" style="vertical-align:top;">
                <b>ÉLÈVE :</b> {{ $eleve?->nom }} {{ $eleve?->postnom }} {{ $eleve?->prenom }}<br>
                <b>NÉ(E) À :</b> {{ $eleve?->lieu_naissance ?: '___________________' }} <b>LE :</b> {{ $eleve?->date_naissance?->format('d/m/Y') ?? '__/__/____' }}<br>
                <b>CLASSE :</b> {{ $classe?->nom ?? '________' }}
            </td>
            <td><b>SEXE :</b> {{ $eleve?->sexe === 'M' ? 'M' : ($eleve?->sexe === 'F' ? 'F' : '___') }}</td>
        </tr>
        <tr>
            <td><b>VILLE :</b> {{ $ouLigne($systemSettings->bulletin_ville) }}<br><b>COMMUNE / TER (1) :</b> {{ $ouLigne($systemSettings->bulletin_commune) }}</td>
            <td><b>MATRICULE :</b> {{ $matricule ?: '—' }}</td>
        </tr>
        <tr>
            <td><b>ÉCOLE :</b> {{ $systemSettings->nom_etablissement ?? '________' }}<br><b>CODE :</b> {{ $ouLigne($systemSettings->bulletin_code_ecole) }}</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:center; font-weight:bold; background-color:#f0f0f0;">
                {{ $bulletinTitre }} — ANNÉE SCOLAIRE {{ $inscription->anneeScolaire?->libelle ?? (\App\Models\AnneeScolaire::enCours()?->libelle ?? '20___ - 20___') }}
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 22%;">BRANCHE</th>
                @foreach ($periodes as $periode)
                    <th colspan="6">{{ $periode->nom }}</th>
                @endforeach
                <th colspan="3">TOTAUX GÉNÉRAUX</th>
            </tr>
            <tr>
                @foreach ($periodes as $periode)
                    <th>M.J.</th><th>T.J.</th><th>M.EX.</th><th>EX.</th><th>TOT</th><th>%</th>
                @endforeach
                <th>TOTAL</th><th>MAX</th><th>%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($branches as $branche)
                <tr>
                    <td class="text-left">{{ $branche['intitule'] }}</td>
                    @foreach ($periodes as $periode)
                        @php($ligne = $branche['periodes'][$periode->id] ?? null)
                        <td>{{ $ligne ? $fmt($ligne['tj_max']) : '' }}</td>
                        <td>{{ $ligne ? $fmt($ligne['tj']) : '' }}</td>
                        <td>{{ $ligne ? $fmt($ligne['examen_max']) : '' }}</td>
                        <td>{{ $ligne ? $fmt($ligne['examen']) : '' }}</td>
                        <td>{{ $ligne ? $fmt($ligne['total']) : '' }}</td>
                        <td>{{ $ligne && $ligne['pourcentage'] !== null ? $fmt($ligne['pourcentage']) : '' }}</td>
                    @endforeach
                    <td>{{ $fmt($branche['total']) }}</td>
                    <td>{{ $fmt($branche['max']) }}</td>
                    <td>{{ $branche['pourcentage'] === null ? '' : $fmt($branche['pourcentage']) }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ 6 * $periodes->count() + 4 }}" class="text-center">Aucune branche affectée à cette classe.</td></tr>
            @endforelse

            @if ($branches)
                <tr style="font-weight:bold; background-color:#e0e0e0;">
                    <td class="text-left">MAXIMA / TOTAUX GÉNÉRAUX</td>
                    @foreach ($periodes as $periode)
                        <td colspan="6"></td>
                    @endforeach
                    <td>{{ $fmt($total_general) }}</td>
                    <td>{{ $fmt($max_general) }}</td>
                    <td>{{ $fmt($pourcentage_general) }}</td>
                </tr>
            @endif

            <tr>
                <td class="text-left"><b>POURCENTAGE GÉNÉRAL</b></td>
                <td colspan="{{ 6 * $periodes->count() }}" class="bg-black"></td>
                <td class="bg-black" colspan="3" style="color:#fff; font-weight:bold;">{{ $fmt($pourcentage_general) }} %</td>
            </tr>
            @if ($afficherPlace)
                <tr>
                    <td class="text-left"><b>PLACE / NBRE D'ÉLÈVES</b></td>
                    <td colspan="{{ 6 * $periodes->count() }}"></td>
                    <td colspan="3">{{ $place ?? '—' }} / {{ $nombre_eleves }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-left"><b>DÉCISION</b></td>
                <td colspan="{{ 6 * $periodes->count() }}"></td>
                <td colspan="3" style="font-weight:bold;">
                    <label><input type="checkbox" disabled @checked($decision === 'Passe')> *PASSE (1)</label><br>
                    <label><input type="checkbox" disabled @checked($decision === 'Double')> *DOUBLE (1)</label>
                </td>
            </tr>
            <tr>
                <td class="text-left"><b>APPLICATION</b></td>
                <td colspan="{{ 6 * $periodes->count() }}"></td>
                <td colspan="3"></td>
            </tr>
            @if ($afficherConduite)
                <tr>
                    <td class="text-left"><b>CONDUITE</b></td>
                    <td colspan="{{ 6 * $periodes->count() }}"></td>
                    <td colspan="3"></td>
                </tr>
            @endif
            <tr>
                <td class="text-left"><b>SIGNATURE</b></td>
                <td colspan="{{ 6 * $periodes->count() }}"></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-section">
        <p>- L'élève ne pourra passer dans la classe supérieure s'il n'a subi avec succès un examen de repêchage en :
            {{ $branches_repechage ? implode(', ', $branches_repechage) : '____________________________' }} (1)
            <span style="color:#555;">(seuil de réussite appliqué : {{ $fmt($seuil_reussite ?? 50) }} %)</span></p>

        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    - L'élève passe dans la classe supérieure (1)<br>
                    - L'élève double la classe (1)
                </td>
                <td style="width: 50%; text-align: right;">
                    Fait à {{ $bulletinLieuSignature }}, le ___ / ___ / 20___<br>
                    <b>{{ $bulletinSignataire }}</b><br>
                    Nom et Signature
                </td>
            </tr>
            <tr>
                <td style="padding-top:20px;">
                    <b>Sceau de l'école</b><br><br>
                    <b>Signature de l'élève</b>
                </td>
                <td></td>
            </tr>
        </table>

        <table class="bas-page">
            <tr>
                <td style="width: 100%;"></td>
                <td style="width: 100px; vertical-align: bottom; text-align: center;">
                    <div id="bulletin-qr" data-qr="{{ $qrBulletin['charge'] }}"></div>
                    <div style="font-size: 8px; margin-top: 2px;">Bulletin unique<br><b>{{ $qrBulletin['code'] }}</b></div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 15px; font-size: 8px;">
            (1) Biffer la mention inutile<br>
            Note importante : Le Bulletin est sans valeur s'il est raturé ou surchargé.
            <span style="float: right;">{{ $bulletinReference }}</span>
        </div>
    </div>
</div>

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
    (function () {
        var cible = document.getElementById('bulletin-qr');
        if (!cible || typeof QRCode === 'undefined') { return; }
        new QRCode(cible, {
            text: cible.getAttribute('data-qr') || '',
            width: 84,
            height: 84,
            correctLevel: QRCode.CorrectLevel.M
        });
    })();
</script>
</body>
</html>
