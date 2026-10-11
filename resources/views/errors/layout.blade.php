@php($etablissement = rescue(fn () => \App\Models\ParametreSysteme::current()?->nom_etablissement, null) ?: config('app.name', 'Établissement scolaire'))
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Erreur') — {{ $etablissement }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
               background:#d0dbe5; color:#1e293b; font-family:'Segoe UI', system-ui, -apple-system, sans-serif; }
        .carte { width:100%; max-width:620px; background:#f2f5f8; border:1px solid #b0c2d0; border-radius:12px;
                 padding:32px; box-shadow:0 6px 24px rgba(15,23,42,.12); text-align:center; }
        .code { font-size:4rem; font-weight:800; line-height:1; color:#0f172a; letter-spacing:2px; }
        .titre { font-size:1.35rem; font-weight:700; margin:12px 0 6px; text-transform:uppercase; }
        .message { color:#475569; margin-bottom:22px; }
        .detail { background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:10px 14px; margin:0 0 22px;
                  font-family:Consolas, monospace; font-size:.78rem; color:#64748b; word-break:break-word; text-align:left; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; }
        .btn { display:inline-block; padding:9px 18px; border-radius:8px; text-decoration:none; font-weight:600; font-size:.85rem; }
        .btn-principal { background:#107c41; color:#fff; }
        .btn-secondaire { background:#fff; color:#0f172a; border:1px solid #94a3b8; }
        .pied { margin-top:20px; font-size:.75rem; color:#64748b; }
    </style>
</head>
<body>
    <div class="carte">
        <div class="code">@yield('code', '500')</div>
        <div class="titre">@yield('titre', 'Erreur')</div>
        <p class="message">@yield('message', "Une erreur inattendue s'est produite.")</p>

        @hasSection('detail')
            <div class="detail">@yield('detail')</div>
        @endif

        <div class="actions">
            <a href="{{ url()->previous() }}" class="btn btn-secondaire">← Retour</a>
            <a href="{{ url('/') }}" class="btn btn-principal">Retour à l’accueil</a>
        </div>

        <div class="pied">{{ $etablissement }} — si le problème persiste, contactez l’administrateur.</div>
    </div>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
