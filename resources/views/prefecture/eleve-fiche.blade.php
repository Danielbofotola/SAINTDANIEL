@extends('prefecture.layout')

@section('titre', "Dossier de l'élève")

@push('styles')
    <style>
        /* Couverture du dossier */
        .dossier-cover { border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,.08); }
        .dossier-cover__band { height: 92px; background: linear-gradient(120deg, #6a1b9a 0%, #0866ff 100%); }
        .dossier-cover__body { padding: 0 22px 20px; margin-top: -46px; }
        .dossier-avatar { width: 104px; height: 104px; border-radius: 50%; background: #fff; border: 4px solid #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,.18); display: flex; align-items: center; justify-content: center;
            font-size: 2.3rem; font-weight: 700; color: #6a1b9a; overflow: hidden; }
        .dossier-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .dossier-nom { font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin: 0; }
        .dossier-meta { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 10px; font-size: .86rem; color: #6c757d; }
        .dossier-meta i { color: #6a1b9a; }

        /* Bloc d'informations : libellé / valeur, lisible et sans tableau */
        .info-bloc { display: flex; justify-content: space-between; align-items: baseline; gap: 14px;
            padding: 8px 0; border-bottom: 1px dashed #e9ecef; font-size: .9rem; }
        .info-bloc:last-child { border-bottom: 0; }
        .info-bloc__cle { color: #6c757d; }
        .info-bloc__val { font-weight: 600; text-align: right; }
        .section-titre { font-size: .95rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .contact-carte { border: 1px solid #e9ecef; border-radius: 10px; padding: 10px 12px; margin-bottom: 8px; }

        /* Version papier du dossier */
        @media print {
            header, .navbar, .sidebar, .main-header, .page-header, .btn, form, .card-category { display: none !important; }
            body, .content, .panel-header { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            .card { border: 1px solid #ccc !important; box-shadow: none !important; break-inside: avoid; }
            .dossier-cover__band { background: #6a1b9a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .col-md-6, .col-md-4, .col-md-3 { width: 50% !important; float: left; }
            a[href]::after { content: ""; }
        }
    </style>
@endpush

@section('actions')
    <div class="btn-group btn-group-page-header">
        <a href="{{ route('prefecture.eleves') }}" class="btn btn-light btn-round">
            <i class="fas fa-arrow-left"></i> Liste des élèves
        </a>
        <button type="button" class="btn btn-light btn-round" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer le dossier
        </button>
        <a href="{{ route('admin.eleves.edit', $eleve) }}" class="btn btn-light btn-round">
            <i class="fas fa-pen"></i> Modifier le dossier
        </a>
    </div>
@endsection

@section('contenu')
    @php
        $nomComplet = trim($eleve->nom.' '.($eleve->postnom ?? '').' '.$eleve->prenom);
        $initiales = mb_strtoupper(mb_substr($eleve->prenom ?: $eleve->nom, 0, 1)).mb_strtoupper(mb_substr($eleve->nom, 0, 1));
        $inscriptionEnCours = $eleve->inscriptions->first(fn ($i) => (int) $i->annee_scolaire_id === (int) ($schoolYear?->id));
        $present = (int) ($parStatut['present'] ?? 0);
        $absent = (int) ($parStatut['absent'] ?? 0);
        $retard = (int) ($parStatut['retard'] ?? 0);
        $excuse = (int) ($parStatut['excuse'] ?? 0);
        $totalPresences = $present + $absent + $retard + $excuse;
        $assiduite = $totalPresences > 0 ? round(($present + $excuse) * 100 / $totalPresences, 1) : 0;
        $alerteMedicale = $eleve->sante && ($eleve->sante->allergies_pathologies || $eleve->sante->traitements_urgences);
        $statuts = ['actif' => ['Actif', 'success'], 'suspendu' => ['Suspendu', 'warning'], 'exclu' => ['Exclu', 'danger'], 'inactif' => ['Inactif', 'secondary']];
        [$libelleStatut, $couleurStatut] = $statuts[$eleve->statut] ?? [$eleve->statut, 'secondary'];
    @endphp

    {{-- ==================== COUVERTURE DU DOSSIER ==================== --}}
    <div class="card dossier-cover mb-4">
        <div class="dossier-cover__band"></div>
        <div class="dossier-cover__body">
            <div class="row align-items-end">
                <div class="col-auto">
                    <div class="dossier-avatar">
                        @if ($eleve->photo)
                            <img src="{{ asset('storage/'.$eleve->photo) }}" alt="{{ $nomComplet }}">
                        @else
                            {{ $initiales }}
                        @endif
                    </div>
                </div>
                <div class="col pt-3">
                    <h1 class="dossier-nom">{{ $nomComplet }}</h1>
                    <div class="dossier-meta">
                        <span><i class="fas fa-id-card"></i> Matricule <strong>{{ $eleve->matricule ?: 'non attribué' }}</strong></span>
                        <span><i class="fas fa-chalkboard"></i> {{ $inscriptionEnCours?->classe?->nom ?? 'Classe non définie' }}</span>
                        @if ($inscriptionEnCours?->classe?->cycle)
                            <span><i class="fas fa-layer-group"></i> {{ $inscriptionEnCours->classe->cycle->nom }}</span>
                        @endif
                        <span><i class="fas fa-calendar-alt"></i> {{ $eleve->date_naissance?->format('d/m/Y') ?? 'naissance non renseignée' }}
                            @if ($eleve->date_naissance) ({{ $eleve->date_naissance->age }} ans) @endif
                        </span>
                        <span><i class="fas fa-school"></i> {{ $schoolYear?->libelle ?? 'année en cours' }}</span>
                    </div>
                    <div class="mt-3">
                        <span class="badge badge-{{ $couleurStatut }}">{{ $libelleStatut }}</span>
                        @if ($eleve->sexe)
                            <span class="badge badge-info">{{ $eleve->sexe === 'F' ? 'Fille' : 'Garçon' }}</span>
                        @endif
                        @if ($alerteMedicale)
                            <span class="badge badge-warning"><i class="fas fa-triangle-exclamation"></i> Suivi médical</span>
                        @endif
                        @if ($eleve->parents->isNotEmpty())
                            <span class="badge badge-primary">{{ $eleve->parents->count() }} compte(s) parent</span>
                        @endif
                        @if ($eleve->documents->isNotEmpty())
                            <span class="badge badge-secondary">{{ $eleve->documents->count() }} document(s)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($alerteMedicale)
        <div class="alert alert-warning">
            <i class="fas fa-notes-medical"></i>
            <strong>Attention médicale :</strong> {{ $eleve->sante->allergies_pathologies ?: $eleve->sante->traitements_urgences }}
        </div>
    @endif

    {{-- ==================== INDICATEURS ==================== --}}
    <div class="row">
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-percentage"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Assiduité</p><h4 class="card-title">{{ $assiduite }}%</h4>
                        <div class="card-category">{{ $totalPresences }} appel(s)</div>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-user-times"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Absences</p><h4 class="card-title">{{ $absent }}</h4>
                        <div class="card-category">{{ $excuse }} excusée(s)</div>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-user-clock"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Retards</p><h4 class="card-title">{{ $retard }}</h4>
                        <div class="card-category">ponctualité</div>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-user-check"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Présences</p><h4 class="card-title">{{ $present }}</h4>
                        <div class="card-category">séances suivies</div>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ==================== IDENTITÉ & DISCIPLINE ==================== --}}
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-title section-titre"><i class="fas fa-id-badge text-primary"></i> État civil</div>
                </div>
                <div class="card-body">
                    <div class="info-bloc"><span class="info-bloc__cle">Nom complet</span><span class="info-bloc__val">{{ $nomComplet }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Matricule</span><span class="info-bloc__val">{{ $eleve->matricule ?: '—' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Sexe</span><span class="info-bloc__val">{{ $eleve->sexe === 'F' ? 'Féminin' : ($eleve->sexe === 'M' ? 'Masculin' : '—') }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Date de naissance</span><span class="info-bloc__val">{{ $eleve->date_naissance?->format('d/m/Y') ?? '—' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Lieu de naissance</span><span class="info-bloc__val">{{ $eleve->lieu_naissance ?: '—' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Classe</span><span class="info-bloc__val">{{ $inscriptionEnCours?->classe?->nom ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        {{-- ==================== DOSSIER MÉDICAL ==================== --}}
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-title section-titre"><i class="fas fa-heart-pulse text-danger"></i> Dossier médical</div>
                </div>
                <div class="card-body">
                    @if ($eleve->sante)
                        <div class="info-bloc"><span class="info-bloc__cle">Groupe sanguin</span><span class="info-bloc__val">{{ $eleve->sante->groupe_sanguin ?: '—' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Allergies / pathologies</span><span class="info-bloc__val">{{ $eleve->sante->allergies_pathologies ?: 'aucune' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Traitements d'urgence</span><span class="info-bloc__val">{{ $eleve->sante->traitements_urgences ?: 'aucun' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Médecin référent</span><span class="info-bloc__val">{{ $eleve->sante->medecin_referent ?: '—' }}</span></div>
                    @else
                        <p class="text-muted mb-0">Aucun dossier médical enregistré pour cet élève.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== STATUT DISCIPLINAIRE ==================== --}}
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-title section-titre"><i class="fas fa-gavel text-warning"></i> Discipline &amp; statut</div>
                </div>
                <div class="card-body">
                    <div class="info-bloc"><span class="info-bloc__cle">Statut actuel</span>
                        <span class="info-bloc__val"><span class="badge badge-{{ $couleurStatut }}">{{ $libelleStatut }}</span></span>
                    </div>
                    <p class="text-muted fs-7 mt-3 mb-2">Décision du préfet : elle conditionne l'accès de l'élève aux cours et sa présence dans les listes.</p>
                    <form method="POST" action="{{ route('prefecture.eleves.statut', $eleve) }}">
                        @csrf
                        @method('PATCH')
                        <label class="mb-1 fw-bold" for="statut-disciplinaire">Nouveau statut</label>
                        <select name="statut" id="statut-disciplinaire" class="form-control mb-2">
                            @foreach (['actif' => 'Actif — suit normalement les cours', 'suspendu' => 'Suspendu — exclu temporairement', 'exclu' => 'Exclu — radiation de l\'établissement'] as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected($eleve->statut === $valeur)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-round btn-block" type="submit">
                            <i class="fas fa-check"></i> Appliquer la décision
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ==================== TUTEUR & CONTACTS ==================== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-title section-titre"><i class="fas fa-user-friends text-info"></i> Tuteur &amp; contacts</div>
                </div>
                <div class="card-body">
                    @if ($eleve->tuteur)
                        @if ($eleve->tuteur->nom_pere)
                            <div class="contact-carte">
                                <div class="d-flex justify-content-between">
                                    <strong>Père — {{ $eleve->tuteur->nom_pere }}</strong>
                                    @if ($eleve->tuteur->profession_pere)<small class="text-muted">{{ $eleve->tuteur->profession_pere }}</small>@endif
                                </div>
                                <div class="fs-7 text-muted"><i class="fas fa-phone"></i> {{ $eleve->tuteur->telephone_pere ?: 'téléphone non renseigné' }}</div>
                            </div>
                        @endif
                        @if ($eleve->tuteur->nom_mere)
                            <div class="contact-carte">
                                <div class="d-flex justify-content-between">
                                    <strong>Mère — {{ $eleve->tuteur->nom_mere }}</strong>
                                    @if ($eleve->tuteur->profession_mere)<small class="text-muted">{{ $eleve->tuteur->profession_mere }}</small>@endif
                                </div>
                                <div class="fs-7 text-muted"><i class="fas fa-phone"></i> {{ $eleve->tuteur->telephone_mere ?: 'téléphone non renseigné' }}</div>
                            </div>
                        @endif
                        @if (! $eleve->tuteur->nom_pere && ! $eleve->tuteur->nom_mere)
                            <p class="text-muted mb-0">Tuteur enregistré mais sans nom de parent.</p>
                        @endif
                        @if ($eleve->tuteur->adresse_pere || $eleve->tuteur->adresse_mere)
                            <div class="fs-7 text-muted mt-2"><i class="fas fa-location-dot"></i> {{ $eleve->tuteur->adresse_pere ?: $eleve->tuteur->adresse_mere }}</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Aucun tuteur renseigné pour cet élève.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== COMPTES PARENTS ==================== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title section-titre"><i class="fas fa-mobile-screen text-primary"></i> Comptes parents rattachés</div>
                        <div class="card-category">{{ $eleve->parents->count() }} compte(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($eleve->parents as $parent)
                        <div class="contact-carte d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ trim(($parent->nom ?? '').' '.($parent->prenom ?? '')) ?: $parent->username }}</strong>
                                <div class="fs-7 text-muted"><i class="fas fa-envelope"></i> {{ $parent->email }}</div>
                            </div>
                            @if ($parent->telephone)
                                <a href="tel:{{ $parent->telephone }}" class="btn btn-sm btn-outline-info btn-round">
                                    <i class="fas fa-phone"></i> {{ $parent->telephone }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun compte parent rattaché. Le rattachement se fait depuis l'administration (fiche élève).</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== INSCRIPTIONS ==================== --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title section-titre"><i class="fas fa-school text-success"></i> Parcours scolaire (inscriptions)</div>
                        <div class="card-category">{{ $eleve->inscriptions->count() }} inscription(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Année</th><th>Classe</th><th>Cycle</th><th>Section / Option</th>
                                <th class="text-right">Moyenne</th><th class="text-right">Exigible</th>
                                <th class="text-right">Payé</th><th class="text-right">Reste</th><th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($parcours as $ligne)
                                @php($inscription = $ligne['inscription'])
                                @php($finances = $ligne['finances'])
                                @php($moyenne = $inscription->bulletins->avg('pourcentage'))
                                <tr>
                                    <td class="fw-bold">
                                        {{ $inscription->anneeScolaire?->libelle ?? '—' }}
                                        @if ($inscription->annee_scolaire_id === $schoolYear?->id)
                                            <span class="badge badge-primary">en cours</span>
                                        @endif
                                    </td>
                                    <td>{{ $inscription->classe?->nom ?? '—' }}</td>
                                    <td>{{ $inscription->classe?->cycle?->nom ?? '—' }}</td>
                                    <td class="fs-7">
                                        {{ $inscription->classe?->section?->nom ?? '—' }}
                                        @if ($inscription->classe?->option?->nom)
                                            <br><span class="text-muted">Option {{ $inscription->classe->option->nom }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $moyenne !== null ? number_format((float) $moyenne, 1).'%' : '—' }}</td>
                                    <td class="text-right">{{ number_format($finances['attendu'], 2) }}</td>
                                    <td class="text-right text-success">{{ number_format($finances['paye'], 2) }}</td>
                                    <td class="text-right {{ $finances['reste'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ number_format($finances['reste'], 2) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $inscription->statut === 'actif' ? 'badge-success' : 'badge-warning' }}">{{ $inscription->statut }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Aucune inscription enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ==================== SITUATION FINANCIÈRE ==================== --}}
    @php($courant = $parcours->first(fn ($ligne) => $ligne['inscription']->annee_scolaire_id === $schoolYear?->id) ?? $parcours->first())
    <div class="row">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-title section-titre"><i class="fas fa-money-bill-wave text-success"></i> Situation financière</div>
                </div>
                <div class="card-body">
                    <div class="info-bloc"><span class="info-bloc__cle">Année de référence</span>
                        <span class="info-bloc__val">{{ $courant['inscription']->anneeScolaire?->libelle ?? '—' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Frais exigibles</span>
                        <span class="info-bloc__val">{{ number_format($courant['finances']['attendu'] ?? 0, 2) }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Total encaissé</span>
                        <span class="info-bloc__val text-success">{{ number_format($courant['finances']['paye'] ?? 0, 2) }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Reste à payer (année)</span>
                        <span class="info-bloc__val {{ ($courant['finances']['reste'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($courant['finances']['reste'] ?? 0, 2) }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Dette cumulée (toutes années)</span>
                        <span class="info-bloc__val {{ $detteTotale > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ number_format($detteTotale, 2) }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title section-titre"><i class="fas fa-receipt text-info"></i> Paiements enregistrés</div>
                        <div class="card-category">{{ $paiements->count() }} reçu(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Reçu</th><th>Date</th><th>Nature</th><th>Période</th><th class="text-right">Montant</th><th>État</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($paiements as $paiement)
                                <tr>
                                    <td class="fw-bold">{{ $paiement->num_recu ?: '—' }}</td>
                                    <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $paiement->fraisScolaire?->typeFrais?->libelle ?? $paiement->fraisScolaire?->typeFrais?->nom ?? 'Frais scolaires' }}</td>
                                    <td>{{ $paiement->periode?->nom ?? '—' }}</td>
                                    <td class="text-right fw-bold">{{ number_format((float) $paiement->montant_paye, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $paiement->statut === 'actif' ? 'badge-success' : 'badge-secondary' }}">{{ $paiement->statut }}</span>
                                        <a href="{{ route('prefecture.paiements.recu', $paiement) }}" target="_blank" rel="noopener"
                                           class="btn btn-info btn-xs btn-round ml-1" title="Imprimer le reçu">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Aucun paiement enregistré pour l'année en cours.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- ==================== DÉTAIL DES FRAIS ==================== --}}
    @if ($courant)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title section-titre">
                                <i class="fas fa-list-check text-success"></i> Détail des frais —
                                {{ $courant['inscription']->anneeScolaire?->libelle ?? 'année de référence' }}
                            </div>
                            <div class="card-category">{{ $courant['frais']['lignes']->count() }} ligne(s) de frais</div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($courant['frais']['lignes']->isEmpty())
                            <p class="text-muted mb-0">
                                <i class="fas fa-circle-info"></i>
                                Aucun frais n'est défini pour cette classe pour
                                {{ $courant['inscription']->anneeScolaire?->libelle ?? "l'année de référence" }}.
                                Les frais se définissent dans <strong>Admin → Frais scolaires</strong>.
                            </p>
                        @else
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nature du frais</th><th>Échéance</th><th class="text-right">Exigible</th>
                                    <th class="text-right">Payé</th><th class="text-right">Reste à payer</th><th>État</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courant['frais']['lignes'] as $ligne)
                                    <tr>
                                        <td class="fw-bold">
                                            {{ $ligne['frais']->typeFrais?->libelle ?? $ligne['frais']->typeFrais?->nom ?? 'Frais scolaires' }}
                                        </td>
                                        <td>{{ $ligne['frais']->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-right">{{ number_format($ligne['exige'], 2) }}</td>
                                        <td class="text-right text-success">{{ number_format($ligne['paye'], 2) }}</td>
                                        <td class="text-right {{ $ligne['reste'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ number_format($ligne['reste'], 2) }}
                                        </td>
                                        <td>
                                            @if ($ligne['reste'] <= 0)
                                                <span class="badge badge-success">Soldé</span>
                                            @elseif ($ligne['paye'] > 0)
                                                <span class="badge badge-warning">Partiel</span>
                                            @else
                                                <span class="badge badge-danger">Non payé</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold" style="background:#f8f9fa">
                                    <td colspan="2">Total de l'année</td>
                                    <td class="text-right">{{ number_format($courant['frais']['totaux']['exige'], 2) }}</td>
                                    <td class="text-right text-success">{{ number_format($courant['frais']['totaux']['paye'], 2) }}</td>
                                    <td class="text-right {{ $courant['frais']['totaux']['reste'] > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($courant['frais']['totaux']['reste'], 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        @if ($courant['frais']['non_affectes'] > 0)
                            <p class="fs-7 text-muted mb-0">
                                <i class="fas fa-circle-info"></i>
                                {{ number_format($courant['frais']['non_affectes'], 2) }} encaissé(s) sans affectation à une ligne de frais
                                (déjà compté(s) dans le total payé).
                            </p>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== ASSIDUITÉ ==================== --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title section-titre"><i class="fas fa-clipboard-check text-info"></i> Assiduité — derniers appels</div>
                        <div class="card-category">{{ $presences->count() }} appel(s) affiché(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Date de séance</th><th>Statut</th><th>Motif</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($presences as $presence)
                                <tr>
                                    <td>{{ $presence->date_seance }}</td>
                                    <td>
                                        @php($code = $presence->statut_presence)
                                        <span class="badge badge-{{ ['present' => 'success', 'excuse' => 'info', 'retard' => 'warning', 'absent' => 'danger'][$code] ?? 'secondary' }}">
                                            {{ \App\Models\Presence::STATUTS[$code] ?? $code }}
                                        </span>
                                    </td>
                                    <td>{{ $presence->motif ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Aucun appel enregistré pour cet élève.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== DOCUMENTS ==================== --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title section-titre"><i class="fas fa-folder-open text-warning"></i> Pièces du dossier</div>
                        <div class="card-category">{{ $eleve->documents->count() }} document(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Type de pièce</th><th>Déposée le</th><th>Statut</th><th class="text-right">Consultation</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($eleve->documents as $document)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-file-lines text-primary"></i>
                                        {{ $documentLabels[$document->type_document] ?? ucfirst($document->type_document) }}
                                    </td>
                                    <td>{{ $document->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ ($document->statut ?? 'actif') === 'actif' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $document->statut ?? 'actif' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ asset('storage/'.$document->chemin_fichier) }}" target="_blank" rel="noopener"
                                           class="btn btn-info btn-sm btn-round">
                                            <i class="fas fa-eye"></i> Ouvrir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun document attaché à ce dossier.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
