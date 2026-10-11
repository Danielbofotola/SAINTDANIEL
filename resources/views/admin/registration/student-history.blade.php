<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique d'inscription</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { min-height: 100vh; background: #f2f5f2; color: #263a36; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 24px; color: #fff; background: #193c39; }
        .page-header a { color: #fff; }
        .page-brand { font-family: Arial, sans-serif; }
        .content { width: min(1080px, calc(100% - 32px)); margin: 28px auto 48px; }
        .heading { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
        .eyebrow { margin-bottom: 6px; color: #71817d; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; color: #193c39; font-size: 26px; font-weight: 800; }
        .heading p { margin: 6px 0 0; color: #71817d; font-size: 12px; }
        .filters { display: flex; gap: 8px; margin-bottom: 18px; }
        .filters a { padding: 7px 14px; color: #304743; background: #fff; border: 1px solid #dbe4dc; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none; }
        .filters a.active { color: #fff; background: #193c39; border-color: #193c39; }
        .request-card { margin-bottom: 13px; padding: 20px; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; box-shadow: 0 8px 22px rgba(25,60,57,.045); }
        .request-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .request-name { margin: 0; color: #193c39; font-size: 16px; font-weight: 800; }
        .request-meta { margin-top: 5px; color: #71817d; font-size: 11px; }
        .request-details { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 17px 0 0; padding: 13px 0 0; border-top: 1px solid #edf1ed; }
        .detail-label { display: block; margin-bottom: 3px; color: #85918b; font-size: 9px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
        .detail-value { color: #344c47; font-size: 12px; font-weight: 600; }
        .reject-reason { margin-top: 14px; padding: 11px 14px; color: #8a3b3b; background: #fdf1f1; border: 1px solid #f3d9d9; border-radius: 7px; font-size: 12px; }
        .empty-state { padding: 48px 20px; color: #71817d; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; text-align: center; }
        .empty-state i { display: block; margin-bottom: 10px; color: #8ca29a; font-size: 24px; }
        .pagination-wrap { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; color: #71817d; font-size: 12px; }
        @media (max-width: 700px) { .page-header { padding: 14px 16px; } .content { width: calc(100% - 24px); margin-top: 20px; } .heading { align-items: flex-start; flex-direction: column; gap: 12px; } .request-details { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    </style>
</head>
<body>
    <header class="page-header"><a href="{{ route('admin.dashboard') }}"><i class="fa fa-arrow-left"></i> Tableau de bord</a><span><span class="page-brand">{{ $systemSettings->nom_court }}</span> · Administration</span></header>
    <main class="content">
        <div class="heading">
            <div><p class="eyebrow">Admissions</p><h1>Historique d'inscription</h1><p>Demandes d'inscription déjà traitées : acceptées et refusées.</p></div>
            <span class="badge badge-info">{{ $requests->total() }} demande(s)</span>
        </div>

        <div class="filters">
            <a href="{{ route('admin.registration.students.history') }}" class="{{ $statut === '' ? 'active' : '' }}">Toutes</a>
            <a href="{{ route('admin.registration.students.history', ['statut' => 'acceptee']) }}" class="{{ $statut === 'acceptee' ? 'active' : '' }}">Acceptées ({{ $counts['acceptee'] }})</a>
            <a href="{{ route('admin.registration.students.history', ['statut' => 'refusee']) }}" class="{{ $statut === 'refusee' ? 'active' : '' }}">Refusées ({{ $counts['refusee'] }})</a>
        </div>

        @forelse ($requests as $request)
            @php($accepted = $request->statut === 'acceptee')
            <article class="request-card">
                <div class="request-top">
                    <div>
                        <h2 class="request-name">{{ $request->nom }} {{ $request->postnom }} {{ $request->prenom }}</h2>
                        <div class="request-meta">Demande #{{ $request->id }} · traitée le {{ $request->traite_le?->format('d/m/Y H:i') ?? 'date inconnue' }}@if ($request->traitePar) · par {{ $request->traitePar->username }}@endif</div>
                    </div>
                    <span class="badge badge-{{ $accepted ? 'success' : 'danger' }}">{{ $accepted ? 'Acceptée' : 'Refusée' }}</span>
                </div>
                <div class="request-details">
                    <div><span class="detail-label">Naissance</span><span class="detail-value">{{ $request->date_naissance?->format('d/m/Y') }} · {{ $request->sexe === 'M' ? 'Garçon' : 'Fille' }}</span></div>
                    <div><span class="detail-label">Responsable</span><span class="detail-value">{{ $request->nom_mere }}</span></div>
                    <div><span class="detail-label">Téléphone</span><span class="detail-value">{{ $request->telephone_mere }}</span></div>
                    <div><span class="detail-label">Classe attribuée</span><span class="detail-value">{{ $request->classe?->nom ?: 'Non attribuée' }}</span></div>
                    <div><span class="detail-label">Année scolaire</span><span class="detail-value">{{ $request->anneeScolaire?->libelle ?: 'Non renseignée' }}</span></div>
                    <div><span class="detail-label">Déposée le</span><span class="detail-value">{{ $request->created_at?->format('d/m/Y H:i') }}</span></div>
                </div>
                @if (! $accepted && $request->motif_refus)
                    <div class="reject-reason"><strong>Motif du refus :</strong> {{ $request->motif_refus }}</div>
                @endif
            </article>
        @empty
            <div class="empty-state">
                <i class="fa fa-inbox"></i>
                @if ($statut === '') Aucune demande d'inscription n'a encore été traitée.
                @elseif ($statut === 'acceptee') Aucune demande acceptée pour le moment.
                @else Aucune demande refusée pour le moment.
                @endif
            </div>
        @endforelse

        @if ($requests->hasPages())
            <div class="pagination-wrap">
                <span>{{ $requests->total() }} demande(s) — page {{ $requests->currentPage() }} / {{ $requests->lastPage() }}</span>
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </main>
</body>
</html>
