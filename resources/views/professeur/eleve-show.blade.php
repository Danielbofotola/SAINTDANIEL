@extends('professeur.layout')

@section('titre', 'Fiche élève')
@section('titre-page')<i class="fa fa-user mr-2"></i> {{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }} @endsection
@section('sous-titre'){{ $eleve->matricule }} · {{ $classeNom ?? 'Classe inconnue' }} · {{ $eleve->sexe === 'F' ? 'Fille' : 'Garçon' }}@endsection

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('professeur.eleves') }}"><i class="fa fa-arrow-left"></i> Mes élèves</a>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-line-chart"></i></div><div class="stat-details"><h4>Moyenne (mes cours)</h4><p>{{ $moyenne !== null ? number_format($moyenne, 2, ',', ' ') : '—' }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-pencil"></i></div><div class="stat-details"><h4>Notes obtenues</h4><p>{{ $notes->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-check-square-o"></i></div><div class="stat-details"><h4>Séances suivies</h4><p>{{ $presences->count() }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon danger"><i class="fa fa-clock-o"></i></div><div class="stat-details"><h4>Absences / retards</h4><p>{{ $presences->whereIn('statut_presence', ['absent', 'retard'])->count() }}</p></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="tile">
                <h3 class="tile-title">Notes dans mes cours <span class="pill ml-2">{{ $notes->count() }}</span></h3>
                @if ($notes->isEmpty())
                    <div class="empty"><i class="fa fa-pencil" aria-hidden="true"></i><strong>Aucune note enregistrée</strong>Les notes apparaîtront dès la première évaluation saisie.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead><tr><th>Date</th><th>Évaluation</th><th>Cours</th><th class="text-right">Note</th></tr></thead>
                            <tbody>
                                @foreach ($notes as $note)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($note->date_evaluation)->format('d/m/Y') }}</td>
                                        <td>{{ $note->evaluation_titre }}<div class="row-meta">{{ $note->observation }}</div></td>
                                        <td>{{ $note->cours_nom }}</td>
                                        <td class="text-right"><strong>{{ rtrim(rtrim(number_format((float) $note->note_obtenue, 2, ',', ' '), '0'), ',') }}</strong> / {{ rtrim(rtrim(number_format((float) $note->max_note, 2, ',', ' '), '0'), ',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="tile">
                <h3 class="tile-title">Dernières présences <span class="pill ml-2">{{ $presences->count() }}</span></h3>
                @forelse ($presences as $presence)
                    <div class="row-item">
                        <time class="date-chip" datetime="{{ $presence->date_seance }}"><strong>{{ \Carbon\Carbon::parse($presence->date_seance)->format('d') }}</strong><span>{{ ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'][(int) \Carbon\Carbon::parse($presence->date_seance)->format('n') - 1] }}</span></time>
                        <div class="row-main">
                            <div class="row-title">{{ \App\Models\Presence::STATUTS[$presence->statut_presence] ?? $presence->statut_presence }}</div>
                            <div class="row-meta">{{ $presence->motif ?: 'Aucun motif' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun appel enregistré pour cet élève.</p>
                @endforelse
            </div>

            @if ($evaluationsSansNote->isNotEmpty())
                <div class="tile">
                    <h3 class="tile-title">Évaluations sans note</h3>
                    @foreach ($evaluationsSansNote->take(6) as $evaluation)
                        <div class="row-item">
                            <div class="row-main"><div class="row-title">{{ $evaluation->titre }}</div><div class="row-meta">{{ $evaluation->cours_nom }} · {{ \Carbon\Carbon::parse($evaluation->date_evaluation)->format('d/m/Y') }}</div></div>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.notes', $evaluation->id) }}"><i class="fa fa-pencil"></i></a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
