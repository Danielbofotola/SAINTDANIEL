<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dossier de {{ $professeur->prenom }} {{ $professeur->nom }}</title>
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
            <div><h1><i class="fa fa-folder-open"></i> Dossier du professeur</h1><p>Identité, compte associé et affectations de l'année en cours.</p></div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.professeurs.index') }}"><i class="fa fa-arrow-left"></i> Retour à la liste</a>
                <a class="btn btn-warning" href="{{ route('admin.professeurs.edit', $professeur) }}"><i class="fa fa-pencil"></i> Modifier</a>
                <a class="btn btn-success" href="{{ route('admin.professeurs.carte', $professeur) }}" target="_blank" rel="noopener"><i class="fa fa-id-card"></i> Carte professionnelle</a>
                <a class="btn btn-primary" href="{{ route('admin.professeurs.emploi-du-temps', $professeur) }}"><i class="fa fa-calendar"></i> Emploi du temps</a>
                <a class="btn btn-info" href="{{ route('admin.affectations.index') }}"><i class="fa fa-list"></i> Cours &amp; affectation</a>
            </div>
        </div>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="tile mb-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    @if ($professeur->photo)<img src="{{ asset('storage/'.$professeur->photo) }}" alt="Photo de {{ $professeur->prenom }}" width="150" height="180" style="object-fit:cover;border-radius:8px">@else<i class="fa fa-user-circle fa-5x text-secondary"></i>@endif
                </div>
                <div class="col-md-9">
                    <h2>{{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }}</h2>
                    <p><strong>Matricule :</strong> {{ $professeur->matricule }}</p>
                    <span class="badge badge-{{ $professeur->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($professeur->statut) }}</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-id-card"></i> Identité professionnelle</h4>
                    <table class="table table-bordered"><tbody>
                        <tr><th>Sexe</th><td>{{ $professeur->sexe === 'M' ? 'Masculin' : 'Féminin' }}</td></tr>
                        <tr><th>Spécialité</th><td>{{ $professeur->specialite }}</td></tr>
                        <tr><th>Téléphone</th><td>{{ $professeur->telephone }}</td></tr>
                        <tr><th>E-mail</th><td>{{ $professeur->email }}</td></tr>
                    </tbody></table>
                </div>
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-key"></i> Compte utilisateur</h4>
                    @if ($professeur->user)
                        <table class="table table-bordered"><tbody>
                            <tr><th>Identifiant</th><td>{{ $professeur->user->username }}</td></tr>
                            <tr><th>Rôle</th><td>{{ ucfirst($professeur->user->role) }}</td></tr>
                            <tr><th>Statut</th><td>{{ ucfirst($professeur->user->statut) }}</td></tr>
                        </tbody></table>
                    @else
                        <p class="text-muted mb-0">Aucun compte n'est rattaché à ce professeur.</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-graduation-cap"></i> Affectations @if($anneeEnCours)<small class="text-muted">{{ $anneeEnCours->libelle }}</small>@endif</h4>
                    @forelse ($professeur->affectationsCours as $affectation)
                        <div class="border-bottom py-2">
                            <strong>{{ $affectation->cours?->intitule ?? 'Cours inconnu' }}</strong>
                            <span class="text-muted">— {{ $affectation->classe?->nom ?? 'Classe inconnue' }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune affectation pour l'année en cours.</p>
                    @endforelse
                </div>
                <div class="tile mb-4">
                    <h4 class="tile-title"><i class="fa fa-files-o"></i> Documents du dossier</h4>
                    @forelse ($professeur->documents as $document)
                        <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-file-text-o text-primary"></i> {{ $document->titre }}<br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $document->type_document)) }} · {{ $document->created_at?->format('d/m/Y') }}@if ($document->taille_ko) · {{ $document->taille_ko }} Ko @endif</small></span>
                            <span class="text-nowrap">
                                <a class="btn btn-sm btn-primary" href="{{ asset('storage/'.$document->chemin_fichier) }}" target="_blank" rel="noopener"><i class="fa fa-eye"></i> Ouvrir</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.professeurs.documents.destroy', $document) }}" onsubmit="return confirm('Supprimer ce document du dossier ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit" title="Supprimer ce document"><i class="fa fa-trash"></i></button>
                                </form>
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun document au dossier.</p>
                    @endforelse

                    <hr>
                    <h5 class="mb-2"><i class="fa fa-plus"></i> Ajouter un document</h5>
                    <form method="POST" action="{{ route('admin.professeurs.documents.store', $professeur) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-5 form-group"><label class="form-label">Titre *</label><input class="form-control" name="titre" value="{{ old('titre') }}" required maxlength="150" placeholder="Ex. Diplôme d’État"></div>
                            <div class="col-md-3 form-group"><label class="form-label">Type *</label><select class="form-control" name="type_document" required><option value="cv">CV</option><option value="diplome">Diplôme</option><option value="contrat">Contrat</option><option value="piece_identite">Pièce d’identité</option><option value="autre">Autre</option></select></div>
                            <div class="col-md-4 form-group"><label class="form-label">Fichier *</label><input class="form-control" type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png"><small class="text-muted">PDF, JPG, JPEG ou PNG — 4 Mo max.</small></div>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> Ajouter au dossier</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
