<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des élèves</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-users"></i> Liste des élèves</h1>
                @php($anneeVue = $annee ?? $anneeEnCours)
                <p>Effectif de l'établissement @if($anneeVue) — année <strong>{{ $anneeVue->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.eleves.create') }}" class="btn btn-primary"><i class="fa fa-user-plus"></i> Inscrire un élève</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Élèves</h4><p><b>{{ $stats['total'] }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-male fa-3x"></i><div class="info"><h4>Garçons</h4><p><b>{{ $stats['garcons'] }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-female fa-3x"></i><div class="info"><h4>Filles</h4><p><b>{{ $stats['filles'] }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small danger coloured-icon"><i class="icon fa fa-graduation-cap fa-3x"></i><div class="info"><h4>Inscrits cette année</h4><p><b>{{ $stats['inscrits'] }}</b></p></div></div></div>
        </div>

        <div class="tile">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <form class="row mb-3" method="GET" action="{{ route('admin.eleves.index') }}">
                <div class="col-md-4 form-group mb-md-0"><input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Rechercher par nom, post-nom, prénom ou matricule…"></div>
                <div class="col-md-3 form-group mb-md-0"><select class="form-control" name="annee" onchange="this.form.submit()">
                    <option value="">Année en cours</option>
                    @foreach ($annees as $a)
                        <option value="{{ $a->id }}" @selected(($annee?->id) === $a->id)>{{ $a->libelle }}@if ($a->is_encours) · en cours @endif</option>
                    @endforeach
                </select></div>
                <div class="col-md-3 form-group mb-md-0"><select class="form-control" name="statut">
                    <option value="">Tous les statuts</option>
                    @foreach (['actif' => 'Actif', 'inactif' => 'Inactif', 'suspendu' => 'Suspendu', 'exclu' => 'Exclu'] as $value => $label)
                        <option value="{{ $value }}" @selected($statut === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div class="col-md-2"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Photo</th><th>Matricule</th><th>Nom complet</th><th>Sexe</th><th>Naissance</th><th>Classe @if(!empty($anneeVue))<small>{{ $anneeVue->libelle }}</small>@endif</th><th>Origine</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($eleves as $eleve)
                            @php($inscription = $eleve->inscriptions->first())
                            <tr>
                                <td>{{ $eleves->firstItem() + $loop->index }}</td>
                                <td class="text-center">@if ($eleve->photo)<img src="{{ asset('storage/'.$eleve->photo) }}" alt="Photo" width="40" height="40" class="rounded-circle" style="object-fit:cover">@else<i class="fa fa-user-circle fa-2x text-secondary"></i>@endif</td>
                                <td>{{ $eleve->matricule ?? '—' }}</td>
                                <td>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</td>
                                <td>{{ $eleve->sexe === 'M' ? 'Garçon' : 'Fille' }}</td>
                                <td>{{ $eleve->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</td>
                                <td>{{ $inscription?->classe?->nom ?? 'Non inscrit' }}@if ($inscription && $inscription->statut === 'en_attente') <span class="badge badge-warning" title="En attente du frais d’inscription">Inscription en attente</span>@endif</td>
                                @php($decision = $origines[$eleve->id] ?? null)
                                <td>
                                    @if ($decision)
                                        <span class="badge badge-{{ ['admis' => 'success', 'ajourne' => 'warning', 'redouble' => 'danger'][$decision] ?? 'secondary' }}"
                                              title="Décision de délibération de l'année précédente">
                                            {{ ['admis' => 'Passage', 'ajourne' => 'Ajourné (repêchage)', 'redouble' => 'Redoublant'][$decision] ?? $decision }}
                                        </span>
                                    @else
                                        <span class="badge badge-info">Nouveau</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ ['actif' => 'success', 'inactif' => 'secondary', 'suspendu' => 'warning', 'exclu' => 'danger'][$eleve->statut] ?? 'secondary' }}">{{ ucfirst($eleve->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.eleves.show', $eleve) }}" class="btn btn-sm btn-info" title="Voir le dossier"><i class="fa fa-folder-open"></i></a>
                                    <a href="{{ route('admin.eleves.edit', $eleve) }}" class="btn btn-sm btn-primary" title="Modifier"><i class="fa fa-pencil"></i></a>
                                    <a href="{{ route('admin.eleves.carte', $eleve) }}" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="Carte d'identité / paiement"><i class="fa fa-id-card"></i></a>
                                    {{-- Activer / désactiver : un élève inactif disparaît des listes. --}}
                                    <form class="d-inline" method="POST" action="{{ route('admin.eleves.toggle', $eleve) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $eleve->statut === 'actif' ? 'warning' : 'success' }}" type="submit"
                                                title="{{ $eleve->statut === 'actif' ? "Désactiver (l'élève disparaît des listes et des effectifs)" : "Activer (l'élève redevient visible)" }}">
                                            <i class="fa fa-power-off"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">Aucun élève ne correspond à cette recherche.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $eleves->total() }} élève(s) — page {{ $eleves->currentPage() }} / {{ $eleves->lastPage() }}</small>
                {{ $eleves->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</body>
</html>
