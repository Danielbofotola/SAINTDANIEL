<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carte professionnelle — {{ $professeur->nom }} {{ $professeur->prenom }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #eef2f6; margin: 0; padding: 20px; color: #0f172a; }
        .barre { max-width: 900px; margin: 0 auto 14px; text-align: right; }
        .carte { width: 360px; height: 226px; margin: 0 auto; border: 1px solid #94a3b8; border-radius: 12px; overflow: hidden; background: #fff; display: flex; flex-direction: column; box-shadow: 0 6px 18px rgba(0,0,0,.12); }
        .entete { background: linear-gradient(135deg, #0f766e 0%, #107c41 100%); color: #fff; padding: 8px 12px; }
        .entete .etab { font-weight: 800; font-size: 12px; text-transform: uppercase; }
        .entete .type { font-size: 10px; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .corps { flex: 1; display: flex; gap: 10px; padding: 10px 12px; }
        .photo { width: 74px; height: 90px; border: 1px solid #94a3b8; border-radius: 6px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .infos { font-size: 11px; line-height: 1.35; }
        .infos .nom { font-weight: 800; font-size: 13px; margin-bottom: 4px; }
        .infos .ligne { color: #334155; }
        .pied { display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; border-top: 1px dashed #cbd5e1; }
        .pied .code { font-size: 8px; color: #475569; font-family: Consolas, monospace; }
        #carte-qr img, #carte-qr canvas { width: 62px; height: 62px; }
        @media print { body { background: #fff; padding: 0; } .barre { display: none; } .carte { box-shadow: none; } }
    </style>
</head>
<body>
@php($nomComplet = trim($professeur->nom.' '.$professeur->postnom.' '.$professeur->prenom))
    <div class="barre"><button onclick="window.print()">🖨️ Imprimer la carte</button></div>

    <div class="carte">
        <div class="entete">
            <div class="etab">{{ $systemSettings->nom_court ?: $systemSettings->nom_etablissement }}</div>
            <div class="type">Carte professionnelle · Carte de paiement</div>
        </div>
        <div class="corps">
            <div class="photo">
                @if ($professeur->photo)
                    <img src="{{ asset('storage/'.$professeur->photo) }}" alt="Photo du professeur">
                @else
                    <svg viewBox="0 0 24 24" width="46" height="46" fill="#9aa5b1" role="img" aria-label="Photo du professeur">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5C21 16.5 17 14 12 14Z"/>
                    </svg>
                @endif
            </div>
            <div class="infos">
                <div class="nom">{{ $nomComplet }}</div>
                <div class="ligne"><strong>Matricule :</strong> {{ $carte['matricule'] ?: '—' }}</div>
                <div class="ligne"><strong>Fonction :</strong> Enseignant</div>
                <div class="ligne"><strong>Spécialité :</strong> {{ $professeur->specialite ?: '—' }}</div>
                <div class="ligne"><strong>Établissement :</strong> {{ $systemSettings->nom_etablissement }}</div>
            </div>
        </div>
        <div class="pied">
            <div class="code">{{ $carte['code'] }}</div>
            <div id="carte-qr" data-qr="{{ $carte['charge'] }}"></div>
        </div>
    </div>

    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        (function () {
            var cible = document.getElementById('carte-qr');
            if (!cible || typeof QRCode === 'undefined') { return; }
            new QRCode(cible, { text: cible.getAttribute('data-qr') || '', width: 62, height: 62, correctLevel: QRCode.CorrectLevel.M });
        })();
    </script>
</body>
</html>
