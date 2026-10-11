@php($utilisateur = auth()->user())
@if ($utilisateur && \Illuminate\Support\Facades\Schema::hasTable('conversations'))
    @php($messagesNonLus = \App\Support\Messagerie::nonLus($utilisateur))
    <a class="messagerie-flottante no-print" href="{{ route('messagerie.index') }}" aria-label="Messagerie" title="Messagerie interne">
        <i class="fa fa-comments"></i>
        @if ($messagesNonLus > 0)
            <span class="messagerie-flottante__badge" data-non-lus>{{ $messagesNonLus > 99 ? '99+' : $messagesNonLus }}</span>
        @endif
    </a>
    <style>
        .messagerie-flottante { position: fixed; right: 16px; bottom: 16px; z-index: 1075; width: 52px; height: 52px; border-radius: 50%;
            background: #107c41; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 21px;
            text-decoration: none; box-shadow: 0 8px 22px rgba(15,23,42,.28); }
        .messagerie-flottante:hover, .messagerie-flottante:focus { background: #0d6335; color: #fff; text-decoration: none; }
        .messagerie-flottante__badge { position: absolute; top: -2px; right: -2px; background: #dc2626; color: #fff; border-radius: 999px;
            font-size: 11px; font-weight: 700; padding: 1px 6px; line-height: 16px; }
        @media print { .messagerie-flottante { display: none !important; } }
    </style>
@endif
