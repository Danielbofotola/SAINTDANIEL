@extends('professeur.layout')

@section('titre', 'Mes élèves')
@section('titre-page')<i class="fa fa-users mr-2"></i> Mes élèves @endsection
@section('sous-titre', $eleves->count().' élève(s) dans vos classes · '.$parClasse->count().' classe(s)')

@section('content')
    <div class="tile">
        <h3 class="tile-title">Rechercher un élève</h3>
        <form class="row" method="GET" action="{{ route('professeur.eleves') }}">
            <div class="col-md-6 form-group">
                <label class="form-label">Nom, postnom, prénom ou matricule</label>
                <input class="form-control" type="search" name="q" value="{{ $recherche }}" placeholder="Ex. Mwamba">
            </div>
            <div class="col-md-4 form-group">
                <label class="form-label">Classe</label>
                <select class="form-control" name="classe">
                    <option value="">Toutes mes classes</option>
                    @foreach ($affectations->unique('classe_id') as $affectation)
                        <option value="{{ $affectation->classe_id }}" @selected($classeFiltree === (int) $affectation->classe_id)>{{ $affectation->classe_nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group d-flex align-items-end">
                <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    @forelse ($parClasse as $classeNom => $liste)
        <div class="tile">
            <h3 class="tile-title">{{ $classeNom }} <span class="pill ml-2">{{ $liste->count() }}</span></h3>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead><tr><th>Matricule</th><th>Nom complet</th><th>Sexe</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($liste as $eleve)
                            <tr>
                                <td>{{ $eleve->matricule }}</td>
                                <td>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</td>
                                <td>{{ $eleve->sexe }}</td>
                                <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('professeur.eleves.show', $eleve->id) }}"><i class="fa fa-eye"></i> Fiche</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="tile">
            <div class="empty"><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève trouvé</strong>Vérifiez la recherche ou vos affectations de cours.</div>
        </div>
    @endforelse
@endsection
