@extends('parent.layout')

@section('titre', 'Mes enfants')

@section('contenu')
    <div class="portail-card">
        <h2><i class="fa-solid fa-graduation-cap text-info"></i> Mes enfants</h2>
        @forelse ($enfants as $enfant)
            @php($inscription = $enfant->inscriptions->first())
            <div class="py-2" style="border-bottom:1px solid var(--divider)">
                <a href="{{ route('parent.enfants', ['enfant' => $enfant->id]) }}#contenu" style="text-decoration:none;color:inherit">
                    <div class="d-flex align-items-center gap-2">
                        <span class="portail-avatar" style="width:40px;height:40px;font-size:.9rem;background:var(--blue)">
                            @if ($enfant->photo)
                                <img src="{{ asset('storage/'.$enfant->photo) }}" alt="">
                            @else
                                {{ mb_strtoupper(mb_substr($enfant->prenom ?: $enfant->nom, 0, 1)) }}
                            @endif
                        </span>
                        <div style="flex:1;min-width:0">
                            <div style="color:#fff;font-weight:600">{{ trim($enfant->nom.' '.$enfant->postnom.' '.$enfant->prenom) }}</div>
                            <div class="fs-7 text-muted-2">
                                {{ $enfant->matricule }}@if ($inscription?->classe) · {{ $inscription->classe->nom }}@endif
                            </div>
                        </div>
                        <span class="fs-7" style="color:var(--blue)">
                            @if ($enfantActif && $enfantActif->id === $enfant->id) Notes affichées @else Notes &amp; travaux › @endif
                        </span>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-muted-2 mb-0 fs-7">Aucun enfant n’est encore rattaché à votre compte. Contactez l’administration.</p>
        @endforelse
    </div>

    @if ($enfantActif)
        @php($totalObtenu = $notes->sum(fn ($note) => (float) $note->note_obtenue))
        @php($totalMax = $notes->sum(fn ($note) => (float) ($note->evaluation?->max_note ?? 0)))

        <div class="portail-card">
            <h2><i class="fa-solid fa-clipboard-list text-warning"></i> Notes de {{ $enfantActif->prenom }}</h2>
            <p class="fs-7 text-muted-2">
                {{ $notes->count() }} note(s) publiée(s) ·
                {{ number_format($totalObtenu, 2) }} / {{ number_format($totalMax, 2) }}
                @if ($totalMax > 0) · {{ round($totalObtenu * 100 / $totalMax, 1) }}% @endif
            </p>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Date</th><th>Cours</th><th>Travail</th><th>Période</th><th class="text-right">Note</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($notes as $note)
                            @php($evaluation = $note->evaluation)
                            <tr>
                                <td>{{ $evaluation?->date_evaluation?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $evaluation?->affectation?->cours?->intitule ?? '—' }}</td>
                                <td>{{ $evaluation?->titre ?? '—' }}</td>
                                <td>{{ $evaluation?->periode?->nom ?? '—' }}</td>
                                <td class="text-right fw-bold">
                                    {{ (float) $note->note_obtenue }} / {{ (float) ($evaluation?->max_note ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted-2 fs-7">Aucune note publiée pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="portail-card">
            <h2><i class="fa-solid fa-book-open text-info"></i> Travaux de la classe</h2>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Date</th><th>Cours</th><th>Titre</th><th>Type</th><th class="text-right">Sa note</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($travaux as $travail)
                            <tr>
                                <td>{{ $travail->date_evaluation?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $travail->affectation?->cours?->intitule ?? '—' }}</td>
                                <td>{{ $travail->titre }}</td>
                                <td>{{ ucfirst((string) $travail->type_evaluation) }}</td>
                                <td class="text-right">
                                    @if ($travail->ma_note !== null)
                                        <strong>{{ (float) $travail->ma_note }}</strong> / {{ (float) $travail->max_note }}
                                    @else
                                        <span class="text-muted-2 fs-7">pas encore noté</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted-2 fs-7">Aucun travail programmé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
