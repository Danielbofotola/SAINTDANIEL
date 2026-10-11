<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messagerie (modération) | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">
    <style>
        :root { --bg:#0b0b0c; --card:#242526; --div:#2f3031; --txt:#e4e6eb; --mut:#b0b3b8; --blue:#0866ff; --red:#e41e3f; }
        body { background:var(--bg); color:var(--txt); margin:0; font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif; }
        .entete { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 20px;
            background:var(--card); border-bottom:1px solid var(--div); position:sticky; top:0; z-index:9; }
        .entete h1 { margin:0; font-size:1.15rem; color:#fff; }
        .entete .sous { color:var(--mut); font-size:.78rem; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:999px; border:none;
            font-size:.82rem; font-weight:700; cursor:pointer; color:#fff; background:var(--blue); text-decoration:none; }
        .btn--neutre { background:#3a3b3c; color:var(--txt); }
        .btn--danger { background:var(--red); }
        .contenu { max-width:1150px; margin:0 auto; padding:16px; }
        .carte { background:var(--card); border:1px solid var(--div); border-radius:12px; padding:16px; margin-bottom:14px; }
        input[type=search], input[type=text] { background:var(--bg); border:1px solid var(--div); color:#fff;
            border-radius:8px; padding:8px 12px; font-size:.88rem; }
        table { width:100%; border-collapse:collapse; font-size:.86rem; }
        th, td { padding:9px 10px; border-bottom:1px solid var(--div); text-align:left; vertical-align:top; }
        th { color:var(--mut); font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; }
        td.sujet { color:#fff; font-weight:600; }
        .pill { display:inline-block; font-size:.72rem; font-weight:700; padding:2px 9px; border-radius:999px;
            background:#3a3b3c; color:var(--txt); margin:0 4px 4px 0; }
        .pill--diffusion { background:#1e3a8a; color:#dbeafe; }
        .apercu { color:var(--mut); font-size:.78rem; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .alerte { padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:.85rem; background:#14532d; color:#dcfce7; }
        .vide { color:var(--mut); padding:20px; text-align:center; }
        .rangee { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    </style>
</head>
<body>

<header class="entete">
    <div>
        <h1><i class="fa-solid fa-comments"></i> Modération de la messagerie</h1>
        <div class="sous">{{ $totalConversations }} conversation(s) · {{ $totalMessages }} message(s) au total</div>
    </div>
    <a class="btn btn--neutre" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Tableau de bord</a>
</header>

<main class="contenu">
    @if (session('success'))<div class="alerte">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alerte" style="background:#7f1d1d;color:#fee2e2">{{ session('error') }}</div>@endif

    <section class="carte">
        <form method="GET" action="{{ route('admin.messagerie.index') }}" class="rangee">
            <input type="search" name="q" value="{{ $recherche }}" placeholder="Filtrer par objet ou par participant…" style="min-width:320px">
            <button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
            @if ($recherche !== '')
                <a class="btn btn--neutre" href="{{ route('admin.messagerie.index') }}">Effacer</a>
            @endif
        </form>
    </section>

    <section class="carte">
        <table>
            <thead>
                <tr>
                    <th>Objet</th><th>Participants</th><th>Messages</th><th>Aperçu</th><th>Dernière activité</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($conversations as $conversation)
                    <tr>
                        <td class="sujet">
                            {{ $conversation->sujet }}
                            @if ($conversation->type === 'diffusion')
                                <span class="pill pill--diffusion">diffusion</span>
                            @endif
                        </td>
                        <td>
                            @foreach ($conversation->participants as $participant)
                                <span class="pill">{{ $participant->nom }} {{ $participant->prenom }}</span>
                            @endforeach
                        </td>
                        <td>{{ $conversation->messages_count }}</td>
                        <td><div class="apercu">{{ $conversation->dernierMessage?->corps ?: '—' }}</div></td>
                        <td>{{ $conversation->derniere_activite_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>
                            <div class="rangee" style="gap:6px">
                                <a class="btn btn--neutre" href="{{ route('admin.messagerie.show', $conversation) }}">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                                <form method="POST" action="{{ route('admin.messagerie.destroy', $conversation) }}"
                                      onsubmit="return confirm('Supprimer définitivement cette conversation et tous ses messages ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn--danger" type="submit"><i class="fa-solid fa-trash"></i> Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vide">Aucune conversation{{ $recherche !== '' ? ' pour cette recherche' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $conversations->links() }}</div>
    </section>
</main>
</body>
</html>
