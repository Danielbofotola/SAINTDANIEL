@php use App\Models\MessageContact; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages de contact</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { min-height: 100vh; background: #f2f5f2; color: #263a36; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 24px; color: #fff; background: #193c39; }
        .page-header a { color: #fff; }
        .page-brand { font-family: Arial, sans-serif; }
        .content { width: min(1080px, calc(100% - 32px)); margin: 28px auto 48px; }
        .heading { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 18px; }
        .eyebrow { margin-bottom: 6px; color: #71817d; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; color: #193c39; font-size: 26px; font-weight: 800; }
        .heading p { margin: 6px 0 0; color: #71817d; font-size: 12px; }
        .filtres { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .filtres a { padding: 6px 14px; color: #4b5f5a; background: #fff; border: 1px solid #e1e8e2; border-radius: 999px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .filtres a:hover { border-color: #b9cdc1; }
        .filtres a.actif { color: #fff; background: #193c39; border-color: #193c39; }
        .message-card { margin-bottom: 13px; padding: 20px; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; box-shadow: 0 8px 22px rgba(25,60,57,.045); }
        .message-card.nouveau { border-left: 4px solid #d98b2b; }
        .message-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .message-nom { margin: 0; color: #193c39; font-size: 16px; font-weight: 800; }
        .message-meta { margin-top: 5px; color: #71817d; font-size: 11px; }
        .message-meta a { color: #2f5d52; font-weight: 600; }
        .message-details { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 15px 0; padding: 12px 0; border-top: 1px solid #edf1ed; border-bottom: 1px solid #edf1ed; }
        .detail-label { display: block; margin-bottom: 3px; color: #85918b; font-size: 9px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
        .detail-value { color: #344c47; font-size: 12px; font-weight: 600; }
        .message-corps { margin: 0; color: #405953; font-size: 13px; line-height: 1.65; white-space: pre-line; }
        .message-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; margin-top: 14px; }
        .empty-state { padding: 48px 20px; color: #71817d; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; text-align: center; }
        .empty-state i { display: block; margin-bottom: 10px; color: #8ca29a; font-size: 24px; }
        .pagination-wrap { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; color: #71817d; font-size: 12px; }
        @media (max-width: 700px) { .page-header { padding: 14px 16px; } .content { width: calc(100% - 24px); margin-top: 20px; } .heading { align-items: flex-start; flex-direction: column; } .message-details { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    </style>
</head>
<body>
    <header class="page-header">
        <a href="{{ route('admin.dashboard') }}"><i class="fa fa-arrow-left"></i> Tableau de bord</a>
        <span><span class="page-brand">{{ $systemSettings->nom_court }}</span> · Administration</span>
    </header>
    <main class="content">
        <div class="heading">
            <div>
                <p class="eyebrow">Communication</p>
                <h1>Messages de contact</h1>
                <p>Messages reçus depuis la page publique {{ route('site.contact') }}.</p>
            </div>
            <div>
                <span class="badge badge-warning">{{ $counts[MessageContact::STATUT_NOUVEAU] }} nouveau(x)</span>
                <span class="badge badge-success">{{ $counts[MessageContact::STATUT_TRAITE] }} traité(s)</span>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <nav class="filtres" aria-label="Filtrer les messages">
            <a class="{{ $statut === '' ? 'actif' : '' }}" href="{{ route('admin.messages-contact.index') }}">Tous ({{ $counts['total'] }})</a>
            <a class="{{ $statut === MessageContact::STATUT_NOUVEAU ? 'actif' : '' }}" href="{{ route('admin.messages-contact.index', ['statut' => MessageContact::STATUT_NOUVEAU]) }}">Nouveaux ({{ $counts[MessageContact::STATUT_NOUVEAU] }})</a>
            <a class="{{ $statut === MessageContact::STATUT_TRAITE ? 'actif' : '' }}" href="{{ route('admin.messages-contact.index', ['statut' => MessageContact::STATUT_TRAITE]) }}">Traités ({{ $counts[MessageContact::STATUT_TRAITE] }})</a>
        </nav>

        @forelse ($messages as $item)
            <article class="message-card {{ $item->estNouveau() ? 'nouveau' : '' }}">
                <div class="message-top">
                    <div>
                        <h2 class="message-nom">{{ $item->nom }}</h2>
                        <div class="message-meta">
                            {{ $item->reference() }} · {{ $item->created_at?->format('d/m/Y H:i') }} ·
                            <a href="mailto:{{ $item->email }}">{{ $item->email }}</a>
                            @if ($item->telephone) · <a href="tel:{{ preg_replace('/\s+/', '', $item->telephone) }}">{{ $item->telephone }}</a>@endif
                        </div>
                    </div>
                    <span class="badge {{ $item->estNouveau() ? 'badge-warning' : 'badge-success' }}">{{ $item->estNouveau() ? 'Nouveau' : 'Traité' }}</span>
                </div>

                <div class="message-details">
                    <div><span class="detail-label">Sujet</span><span class="detail-value">{{ $item->sujet }}</span></div>
                    <div><span class="detail-label">Téléphone</span><span class="detail-value">{{ $item->telephone ?: 'Non renseigné' }}</span></div>
                    <div><span class="detail-label">Traitement</span><span class="detail-value">
                        @if ($item->estNouveau()) En attente
                        @else {{ $item->traite_le?->format('d/m/Y H:i') }}@if ($item->traitePar) · {{ $item->traitePar->username }}@endif
                        @endif
                    </span></div>
                </div>

                <p class="message-corps">{{ $item->message }}</p>

                <div class="message-actions">
                    @if ($item->estNouveau())
                        <form method="POST" action="{{ route('admin.messages-contact.statut', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="{{ MessageContact::STATUT_TRAITE }}">
                            <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Marquer comme traité</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.messages-contact.statut', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="{{ MessageContact::STATUT_NOUVEAU }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-undo"></i> Rouvrir</button>
                        </form>
                    @endif
                    <a class="btn btn-outline-primary" href="mailto:{{ $item->email }}?subject={{ rawurlencode('RE: ' . $item->sujet) }}"><i class="fa fa-reply"></i> Répondre</a>
                    <form method="POST" action="{{ route('admin.messages-contact.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit" onclick="return confirm('Supprimer définitivement ce message ?')"><i class="fa fa-trash"></i> Supprimer</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <i class="fa fa-envelope-o"></i>
                @if ($statut === MessageContact::STATUT_NOUVEAU) Aucun nouveau message.
                @elseif ($statut === MessageContact::STATUT_TRAITE) Aucun message traité pour le moment.
                @else Aucun message reçu pour le moment.
                @endif
            </div>
        @endforelse

        @if ($messages->hasPages())
            <div class="pagination-wrap">
                <span>{{ $messages->total() }} message(s) — page {{ $messages->currentPage() }} / {{ $messages->lastPage() }}</span>
                {{ $messages->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </main>
</body>
</html>
