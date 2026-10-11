<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <title>Dossier de {{ $eleve->prenom }} {{ $eleve->nom }}</title>
    <style>
        :root { --dossier: #6a1b9a; }
        body.app { background: #f4f5f7; }

        /* Couverture du dossier */
        .dossier-cover { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,.08); margin-bottom: 22px; }
        .dossier-cover__band { height: 92px; background: linear-gradient(120deg, var(--dossier) 0%, #0866ff 100%); }
        .dossier-cover__body { padding: 0 24px 22px; margin-top: -48px; }
        .dossier-avatar { width: 108px; height: 108px; border-radius: 50%; background: #fff; border: 4px solid #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,.18); display: flex; align-items: center; justify-content: center;
            font-size: 2.4rem; font-weight: 700; color: var(--dossier); overflow: hidden; }
        .dossier-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .dossier-nom { font-size: 1.55rem; font-weight: 700; margin: 0; line-height: 1.2; }
        .dossier-meta { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 10px; font-size: .86rem; color: #6c757d; }
        .dossier-meta i { color: var(--dossier); }

        /* Cartes et informations */
        .dossier-carte { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,.05); height: 100%; margin-bottom: 20px; }
        .dossier-carte__titre { font-size: 1rem; font-weight: 700; margin: 0 0 14px; display: flex; align-items: center; gap: 9px; }
        .info-bloc { display: flex; justify-content: space-between; align-items: baseline; gap: 14px; padding: 8px 0; border-bottom: 1px dashed #e9ecef; font-size: .9rem; }
        .info-bloc:last-child { border-bottom: 0; }
        .info-bloc__cle { color: #6c757d; }
        .info-bloc__val { font-weight: 600; text-align: right; }
        .contact-carte { border: 1px solid #e9ecef; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; }
        .contact-carte:last-child { margin-bottom: 0; }
        .contact-carte a { color: inherit; text-decoration: none; }
        .contact-carte .ligne { font-size: .86rem; color: #6c757d; margin-top: 4px; }
        .contact-carte .ligne i { width: 16px; color: var(--dossier); }

        @media print {
            .app-title, .no-print, .btn { display: none !important; }
            body.app { background: #fff !important; }
            .app-content { margin: 0 !important; padding: 0 !important; }
            .dossier-carte, .dossier-cover { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
            .dossier-cover__band { background: var(--dossier) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .col-md-6, .col-md-4, .col-md-3 { width: 50% !important; float: left; }
            a[href]::after { content: ""; }
        }
    </style>
</head>
<body class="app">
    @php
        $documentLabels = ['acte_naissance' => 'Acte de naissance', 'bulletin' => 'Bulletin scolaire', 'certificat_medical' => 'Certificat médical', 'autre' => 'Autre document'];
        $nomComplet = trim($eleve->nom.' '.($eleve->postnom ?? '').' '.$eleve->prenom);
        $initiales = mb_strtoupper(mb_substr($eleve->prenom ?: $eleve->nom, 0, 1)).mb_strtoupper(mb_substr($eleve->nom, 0, 1));
        $statuts = ['actif' => ['Actif', 'success'], 'suspendu' => ['Suspendu', 'warning'], 'exclu' => ['Exclu', 'danger'], 'inactif' => ['Inactif', 'secondary']];
        [$libelleStatut, $couleurStatut] = $statuts[$eleve->statut] ?? [ucfirst($eleve->statut), 'secondary'];
        $alerteMedicale = $eleve->sante && ($eleve->sante->allergies_pathologies || $eleve->sante->traitements_urgences);
    @endphp

    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-folder-open"></i> Dossier de l'élève</h1>
                <p>Informations scolaires, médicales et administratives.</p>
            </div>
            <div class="no-print">
                <button type="button" class="btn btn-light" onclick="window.print()"><i class="fa fa-print"></i> Imprimer</button>
                <a class="btn btn-info" href="{{ route('admin.eleves.edit', $eleve) }}"><i class="fa fa-pencil"></i> Modifier le profil</a>
                <a class="btn btn-success" href="{{ route('admin.eleves.carte', $eleve) }}" target="_blank" rel="noopener"><i class="fa fa-id-card"></i> Carte de l'élève</a>
                <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}"><i class="fa fa-arrow-left"></i> Retour</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        {{-- ==================== COUVERTURE ==================== --}}
        <div class="dossier-cover">
            <div class="dossier-cover__band"></div>
            <div class="dossier-cover__body">
                <div class="row align-items-end">
                    <div class="col-auto">
                        <div class="dossier-avatar">
                            @if ($eleve->photo)
                                <img src="{{ asset('storage/'.$eleve->photo) }}" alt="Photo de {{ $eleve->prenom }}">
                            @else
                                {{ $initiales }}
                            @endif
                        </div>
                    </div>
                    <div class="col pt-3">
                        <h1 class="dossier-nom">{{ $nomComplet }}</h1>
                        <div class="dossier-meta">
                            <span><i class="fa fa-id-card"></i> Matricule <strong>{{ $eleve->matricule ?: 'non attribué' }}</strong></span>
                            <span><i class="fa fa-graduation-cap"></i> {{ $inscription?->classe?->nom ?? 'Aucune inscription en cours' }}</span>
                            @if ($inscription?->classe?->cycle)
                                <span><i class="fa fa-sitemap"></i> {{ $inscription->classe->cycle->nom }}</span>
                            @endif
                            <span><i class="fa fa-calendar"></i> {{ $eleve->date_naissance?->format('d/m/Y') ?? 'naissance non renseignée' }}
                                @if ($eleve->date_naissance) ({{ $eleve->date_naissance->age }} ans) @endif
                            </span>
                            <span><i class="fa fa-school"></i> {{ $anneeEnCours?->libelle ?? 'année en cours' }}</span>
                        </div>
                        <div class="mt-3">
                            <span class="badge badge-{{ $couleurStatut }}">{{ $libelleStatut }}</span>
                            @if ($eleve->sexe)<span class="badge badge-info">{{ $eleve->sexe === 'M' ? 'Garçon' : 'Fille' }}</span>@endif
                            @if ($alerteMedicale)<span class="badge badge-warning"><i class="fa fa-exclamation-triangle"></i> Suivi médical</span>@endif
                            @if ($eleve->documents->isNotEmpty())<span class="badge badge-secondary">{{ $eleve->documents->count() }} document(s)</span>@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($alerteMedicale)
            <div class="alert alert-warning">
                <i class="fa fa-medkit"></i> <strong>Attention médicale :</strong>
                {{ $eleve->sante->allergies_pathologies ?: $eleve->sante->traitements_urgences }}
            </div>
        @endif

        <div class="row">
            {{-- ==================== IDENTITÉ ==================== --}}
            <div class="col-md-6">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre"><i class="fa fa-id-card" style="color:var(--dossier)"></i> Identité</h4>
                    <div class="info-bloc"><span class="info-bloc__cle">Nom complet</span><span class="info-bloc__val">{{ $nomComplet }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Sexe</span><span class="info-bloc__val">{{ $eleve->sexe === 'M' ? 'Garçon' : ($eleve->sexe === 'F' ? 'Fille' : 'Non renseigné') }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Matricule</span><span class="info-bloc__val">{{ $eleve->matricule ?: 'Non renseigné' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Date de naissance</span><span class="info-bloc__val">{{ $eleve->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Lieu de naissance</span><span class="info-bloc__val">{{ $eleve->lieu_naissance ?: 'Non renseigné' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Statut</span><span class="info-bloc__val"><span class="badge badge-{{ $couleurStatut }}">{{ $libelleStatut }}</span></span></div>
                </div>
            </div>

            {{-- ==================== SCOLARITÉ ==================== --}}
            <div class="col-md-6">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre">
                        <i class="fa fa-graduation-cap" style="color:var(--dossier)"></i> Scolarité
                        @if ($anneeEnCours)<small class="text-muted ml-2">{{ $anneeEnCours->libelle }}</small>@endif
                    </h4>
                    @if ($inscription)
                        <div class="info-bloc"><span class="info-bloc__cle">Classe</span><span class="info-bloc__val">{{ $inscription->classe?->nom ?? 'Non affecté' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Cycle</span><span class="info-bloc__val">{{ $inscription->classe?->cycle?->nom ?? 'Non renseigné' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Section</span><span class="info-bloc__val">{{ $inscription->classe?->section?->nom ?? 'Non renseignée' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Option</span><span class="info-bloc__val">{{ $inscription->classe?->option?->nom ?? 'Non renseignée' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Date d'inscription</span><span class="info-bloc__val">{{ $inscription->date_inscription?->format('d/m/Y') ?? 'Non renseignée' }}</span></div>
                    @else
                        <p class="text-muted mb-0">Cet élève n'est pas inscrit pour l'année en cours.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ==================== SANTÉ ==================== --}}
            <div class="col-md-6">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre"><i class="fa fa-heartbeat" style="color:var(--dossier)"></i> Santé</h4>
                    <div class="info-bloc"><span class="info-bloc__cle">Groupe sanguin</span><span class="info-bloc__val">{{ $eleve->sante?->groupe_sanguin ?: 'Non renseigné' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Allergies / pathologies</span><span class="info-bloc__val">{{ $eleve->sante?->allergies_pathologies ?: 'Aucune information' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Traitements / urgences</span><span class="info-bloc__val">{{ $eleve->sante?->traitements_urgences ?: 'Aucune information' }}</span></div>
                    <div class="info-bloc"><span class="info-bloc__cle">Médecin référent</span><span class="info-bloc__val">{{ $eleve->sante?->medecin_referent ?: 'Non renseigné' }}</span></div>
                </div>
            </div>

            {{-- ==================== RESPONSABLES LÉGAUX ==================== --}}
            <div class="col-md-6">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre"><i class="fa fa-users" style="color:var(--dossier)"></i> Responsables légaux</h4>

                    @php($tuteur = $eleve->tuteur)
                    @php($fiches = [
                        ['role' => 'Mère / responsable principal', 'nom' => $tuteur?->nom_mere, 'tel' => $tuteur?->telephone_mere, 'email' => $tuteur?->email_mere, 'adresse' => $tuteur?->adresse_mere],
                        ['role' => 'Père', 'nom' => $tuteur?->nom_pere, 'tel' => $tuteur?->telephone_pere, 'email' => $tuteur?->email_pere, 'adresse' => $tuteur?->adresse_pere],
                    ])
                    @php($fiches = array_filter($fiches, fn ($f) => filled($f['nom']) || filled($f['tel']) || filled($f['email'])))

                    @forelse ($fiches as $fiche)
                        <div class="contact-carte">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>{{ $fiche['role'] }} — {{ $fiche['nom'] ?: 'Non renseigné' }}</strong>
                                @if ($fiche['tel'])
                                    <a class="btn btn-sm btn-outline-info" href="tel:{{ $fiche['tel'] }}"><i class="fa fa-phone"></i></a>
                                @endif
                            </div>
                            <div class="ligne"><i class="fa fa-phone"></i> {{ $fiche['tel'] ?: 'Téléphone non renseigné' }}</div>
                            <div class="ligne">
                                <i class="fa fa-envelope"></i>
                                @if ($fiche['email'])
                                    <a href="mailto:{{ $fiche['email'] }}">{{ $fiche['email'] }}</a>
                                @else
                                    E-mail non renseigné
                                @endif
                            </div>
                            <div class="ligne"><i class="fa fa-map-marker"></i> {{ $fiche['adresse'] ?: 'Adresse non renseignée' }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun responsable légal renseigné pour cet élève.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ==================== PARCOURS & HISTORIQUE ==================== --}}
        <div class="row">
            <div class="col-md-12">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre">
                        <i class="fa fa-history" style="color:var(--dossier)"></i> Parcours &amp; historique — toutes les années
                        <small class="text-muted ml-2">{{ $parcours->count() }} inscription(s)</small>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Année</th><th>Classe</th><th>Cycle</th><th>Section / Option</th>
                                    <th class="text-right">Moyenne</th><th class="text-right">Exigible</th>
                                    <th class="text-right">Payé</th><th class="text-right">Reste</th><th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($parcours as $ligne)
                                    @php($i = $ligne['inscription'])
                                    @php($fin = $ligne['finances'])
                                    @php($moyenne = $i->bulletins->avg('pourcentage'))
                                    <tr>
                                        <td class="font-weight-bold">
                                            {{ $i->anneeScolaire?->libelle ?? '—' }}
                                            @if ($i->annee_scolaire_id === $anneeEnCours?->id)
                                                <span class="badge badge-primary">en cours</span>
                                            @endif
                                        </td>
                                        <td>{{ $i->classe?->nom ?? '—' }}</td>
                                        <td>{{ $i->classe?->cycle?->nom ?? '—' }}</td>
                                        <td>
                                            {{ $i->classe?->section?->nom ?? '—' }}
                                            @if ($i->classe?->option?->nom)
                                                <br><small class="text-muted">Option {{ $i->classe->option->nom }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $moyenne !== null ? number_format((float) $moyenne, 1).'%' : '—' }}</td>
                                        <td class="text-right">{{ number_format($fin['attendu'], 2) }}</td>
                                        <td class="text-right text-success">{{ number_format($fin['paye'], 2) }}</td>
                                        <td class="text-right {{ $fin['reste'] > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            {{ number_format($fin['reste'], 2) }}
                                        </td>
                                        <td><span class="badge badge-{{ $i->statut === 'actif' ? 'success' : 'warning' }}">{{ $i->statut }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">Aucune inscription enregistrée.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== SITUATION FINANCIÈRE ==================== --}}
        @php($courant = $parcours->first(fn ($l) => $l['inscription']->annee_scolaire_id === $anneeEnCours?->id) ?? $parcours->first())
        <div class="row">
            <div class="col-md-4">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre"><i class="fa fa-money" style="color:var(--dossier)"></i> Situation financière</h4>
                    @if ($courant)
                        <div class="info-bloc"><span class="info-bloc__cle">Année de référence</span>
                            <span class="info-bloc__val">{{ $courant['inscription']->anneeScolaire?->libelle ?? '—' }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Frais exigibles</span>
                            <span class="info-bloc__val">{{ number_format($courant['finances']['attendu'], 2) }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Total encaissé</span>
                            <span class="info-bloc__val text-success">{{ number_format($courant['finances']['paye'], 2) }}</span></div>
                        <div class="info-bloc"><span class="info-bloc__cle">Reste à payer (année)</span>
                            <span class="info-bloc__val {{ $courant['finances']['reste'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($courant['finances']['reste'], 2) }}</span></div>
                    @endif
                    <div class="info-bloc"><span class="info-bloc__cle">Dette cumulée (toutes années)</span>
                        <span class="info-bloc__val {{ $detteTotale > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($detteTotale, 2) }}</span></div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre">
                        <i class="fa fa-list-alt" style="color:var(--dossier)"></i> Suivi des paiements
                        <small class="text-muted ml-2">{{ $paiements->count() }} reçu(s)</small>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Reçu</th><th>Date</th><th>Nature</th><th>Période</th><th class="text-right">Montant</th><th>État</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($paiements as $paiement)
                                    <tr>
                                        <td class="font-weight-bold">{{ $paiement->num_recu ?: '—' }}</td>
                                        <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $paiement->fraisScolaire?->typeFrais?->libelle ?? $paiement->fraisScolaire?->typeFrais?->nom ?? 'Frais scolaires' }}</td>
                                        <td>{{ $paiement->periode?->nom ?? '—' }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format((float) $paiement->montant_paye, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $paiement->statut === 'actif' ? 'success' : 'secondary' }}">{{ $paiement->statut }}</span>
                                            <a href="{{ route('admin.paiements.recu', $paiement) }}" target="_blank" rel="noopener"
                                               class="btn btn-sm btn-info ml-1" title="Imprimer le reçu">
                                                <i class="fa fa-print"></i>
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

        {{-- ==================== DÉTAIL DES FRAIS ==================== --}}
        @if ($courant)
            <div class="row">
                <div class="col-md-12">
                    <div class="dossier-carte">
                        <h4 class="dossier-carte__titre">
                            <i class="fa fa-list-alt" style="color:var(--dossier)"></i> Détail des frais —
                            {{ $courant['inscription']->anneeScolaire?->libelle ?? 'année de référence' }}
                            <small class="text-muted ml-2">{{ $courant['frais']['lignes']->count() }} ligne(s)</small>
                        </h4>
                        @if ($courant['frais']['lignes']->isEmpty())
                            <p class="text-muted mb-0">
                                <i class="fa fa-info-circle"></i>
                                Aucun frais n'est défini pour cette classe pour
                                {{ $courant['inscription']->anneeScolaire?->libelle ?? "l'année de référence" }}.
                                Les frais se définissent dans <strong>Admin → Frais scolaires</strong>.
                            </p>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nature du frais</th><th>Échéance</th><th class="text-right">Exigible</th>
                                        <th class="text-right">Payé</th><th class="text-right">Reste à payer</th><th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($courant['frais']['lignes'] as $ligne)
                                        <tr>
                                            <td class="font-weight-bold">
                                                {{ $ligne['frais']->typeFrais?->libelle ?? $ligne['frais']->typeFrais?->nom ?? 'Frais scolaires' }}
                                            </td>
                                            <td>{{ $ligne['frais']->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-right">{{ number_format($ligne['exige'], 2) }}</td>
                                            <td class="text-right text-success">{{ number_format($ligne['paye'], 2) }}</td>
                                            <td class="text-right {{ $ligne['reste'] > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
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
                                    <tr class="font-weight-bold" style="background:#f8f9fa">
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
                        </div>
                        @if ($courant['frais']['non_affectes'] > 0)
                            <p class="text-muted mb-0" style="font-size:.85rem">
                                <i class="fa fa-info-circle"></i>
                                {{ number_format($courant['frais']['non_affectes'], 2) }} encaissé(s) sans affectation à une ligne de frais
                                (déjà compté(s) dans le total payé).
                            </p>
                        @endif
                    @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== DOCUMENTS ==================== --}}
        <div class="row">
            <div class="col-md-12">
                <div class="dossier-carte">
                    <h4 class="dossier-carte__titre">
                        <i class="fa fa-files-o" style="color:var(--dossier)"></i> Documents et fichiers attachés
                        <small class="text-muted ml-2">{{ $eleve->documents->count() }} pièce(s)</small>
                    </h4>
                    @forelse ($eleve->documents as $document)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span>
                                <i class="fa fa-file-text-o" style="color:var(--dossier)"></i>
                                <strong>{{ $documentLabels[$document->type_document] ?? ucfirst($document->type_document) }}</strong>
                                <br><small class="text-muted">Déposé le {{ $document->created_at?->format('d/m/Y') ?? '—' }}</small>
                            </span>
                            <a class="btn btn-sm btn-primary" href="{{ asset('storage/'.$document->chemin_fichier) }}" target="_blank" rel="noopener">
                                <i class="fa fa-eye"></i> Ouvrir
                            </a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun document attaché à ce dossier.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</body>
</html>
