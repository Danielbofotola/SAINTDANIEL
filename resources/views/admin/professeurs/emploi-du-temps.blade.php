<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emploi du temps — {{ $professeur->prenom }} {{ $professeur->nom }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        :root { --canvas: #d0dbe5; --card-bg: #f2f5f8; --card-border: #b0c2d0; }
        body { background-color: var(--canvas); color: #1e293b; }
        .prof-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 8px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
        .card-title-header { font-size: .95rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; text-transform: uppercase; }
        .table-schedule { font-size: .8rem; background: #fff; border-radius: 6px; table-layout: fixed; }
        .table-schedule th { background: #cbd5e1; color: #0f172a; font-weight: 700; text-align: center; vertical-align: middle; padding: 10px 4px; }
        .table-schedule td { height: 85px; vertical-align: top; padding: 4px; background: #f8fafc; }
        .time-col { background: #e2e8f0 !important; font-weight: 700; color: #334155; text-align: center; vertical-align: middle !important; width: 90px; font-size: .75rem; }
        .today-header { background: #0284c7 !important; color: #fff !important; }
        .course-box { border-radius: 5px; padding: 6px 8px; margin-bottom: 4px; height: 100%; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-left: 4px solid #0284c7; }
        .course-blue { background: #e0f2fe; border-left-color: #0284c7; color: #0369a1; }
        .course-amber { background: #fef3c7; border-left-color: #d97706; color: #92400e; }
        .course-green { background: #dcfce7; border-left-color: #16a34a; color: #15803d; }
        .course-purple { background: #f3e8ff; border-left-color: #9333ea; color: #6b21a8; }
        .course-title { font-weight: 700; font-size: .78rem; line-height: 1.1; margin-bottom: 2px; }
        .course-meta { font-size: .7rem; display: flex; justify-content: space-between; align-items: center; font-weight: 600; gap: 4px; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .table-schedule td { height: 70px; } }
    </style>
</head>
<body class="p-3">
@php
    $palette = ['course-blue', 'course-amber', 'course-green', 'course-purple'];
    $aujourdhui = \Illuminate\Support\Carbon::now()->locale('fr')->dayName;
@endphp

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="prof-card d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <div class="card-title-header mb-0"><i class="fa fa-calendar"></i> Emploi du temps hebdomadaire</div>
                    <small class="text-muted">{{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }}@if ($annee) · année <strong>{{ $annee->libelle }}</strong>@endif</small>
                </div>
                <div class="no-print">
                    <a href="{{ route('admin.professeurs.show', $professeur) }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Retour à la fiche</a>
                    <a href="{{ route('admin.emplois-du-temps.index', ['professeur_id' => $professeur->id]) }}" class="btn btn-sm btn-outline-primary bg-white"><i class="fa fa-sliders"></i> Gérer les créneaux</a>
                    <button class="btn btn-sm btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Imprimer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="prof-card">
                @if ($creneaux->isEmpty())
                    <p class="text-muted mb-0">Aucun créneau planifié pour ce professeur cette année. Ajoutez-en depuis « Horaire &amp; Emploi du temps ».</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-schedule mb-0">
                            <thead>
                                <tr>
                                    <th class="time-col"><i class="fa fa-clock-o"></i> Heures</th>
                                    @foreach ($jours as $jour)
                                        <th class="{{ $jour === $aujourdhui ? 'today-header' : '' }}">{{ ucfirst($jour) }}@if ($jour === $aujourdhui) (aujourd'hui)@endif</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($creneaux as $creneau)
                                    @php($cle = $creneau['debut'].'|'.$creneau['fin'])
                                    <tr>
                                        <td class="time-col">{{ substr($creneau['debut'], 0, 5) }} - {{ substr($creneau['fin'], 0, 5) }}</td>
                                        @foreach ($jours as $jour)
                                            <td>
                                                @foreach ($grille[$jour][$cle] ?? [] as $entree)
                                                    @php($cours = $entree->affectation?->cours)
                                                    <div class="course-box {{ $palette[($cours?->id ?? 0) % 4] }}">
                                                        <div class="course-title">{{ $cours?->intitule ?? 'Cours' }}</div>
                                                        <div class="course-meta">
                                                            <span><i class="fa fa-users"></i> {{ $entree->affectation?->classe?->nom ?? '—' }}</span>
                                                            <span class="badge badge-light border">{{ $entree->salle?->nom ?? 'Salle —' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top">
                        <div class="d-flex flex-wrap align-items-center">
                            <small class="font-weight-bold text-muted mr-3">Légende (couleur par cours) :</small>
                            @foreach ($grille as $jour => $slots)
                                @foreach ($slots as $entrees)
                                    @foreach ($entrees as $entree)
                                        @php($cours = $entree->affectation?->cours)
                                        <span class="badge badge-light border mr-1 mb-1">{{ $cours?->intitule ?? 'Cours' }}</span>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>
                        <div class="text-muted small font-weight-bold">Total : <span class="text-dark">{{ $totalHeures }} h / semaine</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</body>
</html>
