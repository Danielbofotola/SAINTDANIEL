{{--
    Grille horaire hebdomadaire réutilisable.

    Paramètres attendus :
      $seances           : liste plate de séances, chaque entrée = [
                               'jour', 'debut', 'fin', 'cours', 'classe', 'professeur', 'salle',
                           ]
      $titre             : titre de la grille (classe ou enseignant) — optionnel
      $afficherClasse    : affiche la classe dans les boîtes de cours (vue enseignant)
      $afficherProfesseur: affiche le professeur dans les boîtes de cours (vue classe)
      $jours             : liste des jours — optionnel (défaut lundi → samedi)
      $aujourdhui        : jour courant à mettre en avant — optionnel
      $pause             : créneau de pause, ex. ['debut' => '11:00', 'fin' => '11:30', 'label' => 'Pause Récréation'] — optionnel
      $vide              : message affiché quand la grille est vide — optionnel
--}}
@php
    $jours = $jours ?? ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $afficherClasse = $afficherClasse ?? false;
    $afficherProfesseur = $afficherProfesseur ?? false;
    $seances = collect($seances ?? []);
    $palette = ['course-blue', 'course-amber', 'course-green', 'course-purple'];
    $aujourdhui = $aujourdhui ?? null;

    // Couleur stable attribuée à chaque cours selon son ordre d'apparition.
    $couleurs = [];
    foreach ($seances as $seance) {
        $cours = $seance['cours'] ?? 'Cours';
        if (! array_key_exists($cours, $couleurs)) {
            $couleurs[$cours] = $palette[count($couleurs) % count($palette)];
        }
    }

    // Créneaux horaires uniques (début|fin), triés chronologiquement.
    $slots = [];
    foreach ($seances as $seance) {
        $cle = ($seance['debut'] ?? '') . '|' . ($seance['fin'] ?? '');
        $slots[$cle] = ['debut' => $seance['debut'] ?? '', 'fin' => $seance['fin'] ?? '', 'pause' => false];
    }
    if ($pause) {
        $slots[$pause['debut'] . '|' . $pause['fin']] = [
            'debut' => $pause['debut'], 'fin' => $pause['fin'], 'pause' => true,
            'label' => $pause['label'] ?? 'Pause',
        ];
    }
    $slots = collect($slots)->sortBy('debut')->values();

    // Grille [jour][clé créneau] = séances.
    $grille = [];
    foreach ($seances as $seance) {
        $grille[$seance['jour']][($seance['debut'] ?? '') . '|' . ($seance['fin'] ?? '')][] = $seance;
    }

    $totalMinutes = $seances->sum(fn ($s) => strtotime($s['fin']) - strtotime($s['debut'])) / 60;
    $totalHeures = round($totalMinutes / 60, 1);
    $totalHeures = (fmod($totalHeures, 1.0) == 0.0) ? (int) $totalHeures : $totalHeures;
@endphp

<div class="horaire-entity">
    @if (! empty($titre))
        <div class="horaire-entity-title">
            <i class="fa fa-graduation-cap mr-1"></i> {{ $titre }}
            @isset($sousTitre)<small class="text-muted font-weight-normal ml-2">{{ $sousTitre }}</small>@endisset
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-schedule align-middle mb-0" data-datatable="off">
            <thead>
                <tr>
                    <th class="time-col"><i class="fa fa-clock-o mr-1"></i> Heures</th>
                    @foreach ($jours as $jour)
                        <th class="{{ $jour === $aujourdhui ? 'today-header' : '' }}">
                            {{ ucfirst($jour) }}@if ($jour === $aujourdhui) (Aujourd'hui)@endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($slots as $slot)
                    @php $cleSlot = $slot['debut'] . '|' . $slot['fin']; @endphp
                    @if ($slot['pause'])
                        <tr class="pause-row">
                            <td class="time-col">{{ $slot['debut'] }} - {{ $slot['fin'] }}</td>
                            <td colspan="{{ count($jours) }}"><i class="fa fa-coffee mr-2"></i> {{ $slot['label'] }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="time-col">{{ $slot['debut'] }} - {{ $slot['fin'] }}</td>
                            @foreach ($jours as $jour)
                                <td>
                                    @foreach ($grille[$jour][$cleSlot] ?? [] as $seance)
                                        <div class="course-box {{ $couleurs[$seance['cours'] ?? 'Cours'] ?? 'course-blue' }}">
                                            <div class="course-title">{{ $seance['cours'] ?? 'Cours' }}</div>
                                            <div class="course-meta">
                                                @if ($afficherProfesseur && ! empty($seance['professeur']))
                                                    <span><i class="fa fa-user mr-1"></i>{{ $seance['professeur'] }}</span>
                                                @elseif ($afficherClasse && ! empty($seance['classe']))
                                                    <span><i class="fa fa-users mr-1"></i>{{ $seance['classe'] }}</span>
                                                @endif
                                                <span class="badge badge-light border">{{ $seance['salle'] ?? 'Salle —' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td class="time-col">—</td>
                        <td colspan="{{ count($jours) }}" class="text-muted text-center align-middle">
                            {{ $vide ?? 'Aucun créneau planifié.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($seances->isNotEmpty())
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top">
            <div class="d-flex flex-wrap align-items-center">
                <small class="font-weight-bold text-muted mr-3">Légende :</small>
                @foreach ($couleurs as $cours => $couleur)
                    <span class="badge badge-light border mr-1 mb-1">
                        <span class="couleur-point {{ $couleur }}"></span> {{ $cours }}
                    </span>
                @endforeach
            </div>
            <div class="text-muted small font-weight-bold">
                Total : <span class="text-dark">{{ $totalHeures }} h / semaine</span>
            </div>
        </div>
    @endif
</div>
