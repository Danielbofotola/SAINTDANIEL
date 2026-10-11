@extends('professeur.layout')

@section('titre', 'Détail du cours')
@section('titre-page')<i class="fa fa-book mr-2"></i> {{ $detail?->cours_nom ?? 'Cours' }} @endsection
@section('sous-titre')
    {{ $detail?->classe_nom }}
    @if ($detail?->section_nom) · {{ $detail->section_nom }}@endif
    @if ($detail?->option_nom) · {{ $detail->option_nom }}@endif
@endsection

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('professeur.cours') }}"><i class="fa fa-arrow-left"></i> Mes cours</a>
    <a class="btn btn-primary" href="{{ route('professeur.evaluations') }}"><i class="fa fa-pencil-square-o"></i> Évaluations</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="tile">
                <h3 class="tile-title">Élèves de la classe <span class="pill ml-2">{{ $eleves->count() }}</span></h3>
                @if ($eleves->isEmpty())
                    <div class="empty"><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève inscrit</strong>Les élèves apparaîtront dès les inscriptions de la classe.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead><tr><th>Matricule</th><th>Nom complet</th><th>Sexe</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($eleves as $eleve)
                                    <tr>
                                        <td>{{ $eleve->matricule }}</td>
                                        <td>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</td>
                                        <td>{{ $eleve->sexe }}</td>
                                        <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.eleves.show', $eleve->id) }}"><i class="fa fa-eye"></i></a></td>
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
                <h3 class="tile-title">Créneaux de la semaine</h3>
                @forelse ($emplois as $creneau)
                    <div class="row-item">
                        <div class="row-time">{{ substr($creneau->heure_debut, 0, 5) }}<small>{{ substr($creneau->heure_fin, 0, 5) }}</small></div>
                        <div class="row-main"><div class="row-title" style="text-transform:capitalize">{{ $creneau->jour }}</div><div class="row-meta">{{ $creneau->salle_nom ?: 'Salle à confirmer' }}</div></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun créneau à l’emploi du temps pour ce cours.</p>
                @endforelse
            </div>

            <div class="tile">
                <h3 class="tile-title">Évaluations <span class="pill ml-2">{{ $evaluations->count() }}</span></h3>
                @forelse ($evaluations as $evaluation)
                    @php($d = \Carbon\Carbon::parse($evaluation->date_evaluation))
                    <div class="row-item">
                        <time class="date-chip" datetime="{{ $d->toDateString() }}"><strong>{{ $d->format('d') }}</strong><span>{{ ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'][(int) $d->format('n') - 1] }}</span></time>
                        <div class="row-main">
                            <div class="row-title">{{ $evaluation->titre }}</div>
                            <div class="row-meta">{{ $evaluation->notes_count }} note(s) / max {{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, ',', ' '), '0'), ',') }}</div>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.notes', $evaluation->id) }}"><i class="fa fa-pencil"></i></a>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune évaluation pour ce cours.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
