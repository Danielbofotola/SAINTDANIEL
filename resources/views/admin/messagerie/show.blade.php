<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $conversation->sujet }} | Modération</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">
    <style>
        :root { --bg:#0b0b0c; --card:#242526; --div:#2f3031; --txt:#e4e6eb; --mut:#b0b3b8; --blue:#0866ff; --red:#e41e3f; }
        body { background:var(--bg); color:var(--txt); margin:0; font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif; }
        .entete { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 20px;
            background:var(--card); border-bottom:1px solid var(--div); position:sticky; top:0; z-index:9; }
        .entete h1 { margin:0; font-size:1.1rem; color:#fff; }
        .entete .sous { color:var(--mut); font-size:.78rem; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:999px; border:none;
            font-size:.82rem; font-weight:700; cursor:pointer; color:#fff; background:var(--blue); text-decoration:none; }
        .btn--neutre { background:#3a3b3c; color:var(--txt); }
        .btn--danger { background:var(--red); }
        .btn--mini { padding:4px 10px; font-size:.74rem; }
        .contenu { max-width:900px; margin:0 auto; padding:16px; }
        .carte { background:var(--card); border:1px solid var(--div); border-radius:12px; padding:16px; margin-bottom:14px; }
        .pill { display:inline-block; font-size:.72rem; font-weight:700; padding:2px 9px; border-radius:999px;
            background:#3a3b3c; color:var(--txt); margin:0 4px 4px 0; }
        .msg { display:flex; gap:10px; padding:10px 0; border-bottom:1px solid var(--div); align-items:flex-start; }
        .msg__avatar { width:34px; height:34px; border-radius:50%; background:var(--blue); color:#fff; flex:none;
            display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; }
        .msg__auteur { color:#fff; font-weight:700; font-size:.85rem; }
        .msg__heure { color:var(--mut); font-size:.72rem; }
        .msg__corps { margin-top:3px; font-size:.88rem; white-space:pre-wrap; word-break:break-word; }
        .msg__piece { margin-top:4px; font-size:.78rem; color:var(--mut); }
        .alerte { padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:.85rem; background:#14532d; color:#dcfce7; }
        .vide { color:var(--mut); padding:18px; text-align:center; }
        .rangee { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    </style>
</head>
<body>

<header class="entete">
    <div>
        <h1><i class="fa-solid fa-comment-dots"></i> {{ $conversation->sujet }}</h1>
        <div class="sous">
            {{ $conversation->libelleType() }} ·
            créée par {{ $conversation->createur?->nom ?? 'compte supprimé' }} le
            {{ $conversation->created_at?->format('d/m/Y H:i') }}
        </div>
    </div>
    <div class="rangee">
        <a class="btn btn--neutre" href="{{ route('admin.messagerie.index') }}"><i class="fa-solid fa-arrow-left"></i> Toutes les conversations</a>
        <form method="POST" action="{{ route('admin.messagerie.destroy', $conversation) }}"
              onsubmit="return confirm('Supprimer définitivement cette conversation et tous ses messages ?')">
            @csrf @method('DELETE')
            <button class="btn btn--danger" type="submit"><i class="fa-solid fa-trash"></i> Supprimer la conversation</button>
        </form>
    </div>
</header>

<main class="contenu">
    @if (session('success'))<div class="alerte">{{ session('success') }}</div>@endif

    <section class="carte">
        <strong style="color:#fff;font-size:.85rem">Participants</strong>
        <div style="margin-top:8px">
            @foreach ($conversation->participants as $participant)
                <span class="pill">{{ $participant->nom }} {{ $participant->postnom }} {{ $participant->prenom }}
                    · {{ \App\Models\User::libelleRole($participant->role) }}</span>
            @endforeach
        </div>
    </section>

    <section class="carte">
        <strong style="color:#fff;font-size:.85rem">Messages ({{ $messages->count() }})</strong>
        <div style="margin-top:8px">
            @forelse ($messages as $message)
                <div class="msg">
                    @if ($message->auteur?->photo)
                        <img src="{{ asset('storage/'.$message->auteur->photo) }}" alt="" class="msg__avatar" style="object-fit:cover">
                    @else
                        <span class="msg__avatar">{{ mb_strtoupper(mb_substr($message->auteur?->prenom ?: '?', 0, 1)) }}</span>
                    @endif
                    <div style="flex:1;min-width:0">
                        <div class="rangee" style="justify-content:space-between">
                            <span>
                                <span class="msg__auteur">{{ $message->nomAuteur() }}</span>
                                <span class="msg__heure"> · {{ $message->created_at?->format('d/m/Y H:i') }}</span>
                            </span>
                            <form method="POST" action="{{ route('admin.messagerie.messages.destroy', $message) }}"
                                  onsubmit="return confirm('Supprimer ce message ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn--danger btn--mini" type="submit"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                        <div class="msg__corps">{{ $message->corps }}</div>
                        @if ($message->piece_jointe)
                            <div class="msg__piece">
                                <i class="fa-solid fa-paperclip"></i>
                                <a href="{{ $message->urlPieceJointe() }}" target="_blank" rel="noopener" style="color:var(--blue)">{{ $message->nomPieceJointe() }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="vide">Aucun message dans cette conversation.</div>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
