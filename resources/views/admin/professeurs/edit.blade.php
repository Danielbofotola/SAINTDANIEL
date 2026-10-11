<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier {{ $professeur->prenom }} {{ $professeur->nom }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-pencil-square-o"></i> Modifier le professeur</h1>
                <p>Identité professionnelle, photo de profil et statut — matricule <strong>{{ $professeur->matricule }}</strong>.</p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.professeurs.show', $professeur) }}"><i class="fa fa-arrow-left"></i> Retour à la fiche</a>
                <a class="btn btn-info" href="{{ route('admin.professeurs.index') }}"><i class="fa fa-list"></i> Liste</a>
            </div>
        </div>

        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-id-card"></i> Profil du professeur</h4>
            <form method="POST" action="{{ route('admin.professeurs.update', $professeur) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if ($professeur->photo)
                            <img src="{{ asset('storage/'.$professeur->photo) }}" alt="Photo" width="150" height="180" style="object-fit:cover;border-radius:8px">
                        @else
                            <i class="fa fa-user-circle fa-5x text-secondary"></i>
                        @endif
                        <div class="form-group mt-2">
                            <label class="form-label">Nouvelle photo</label>
                            <input class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png">
                            <small class="text-muted">JPG, JPEG ou PNG, 2 Mo maximum.</small>
                        </div>
                        @if ($professeur->photo)
                            <div class="form-check text-left">
                                <input class="form-check-input" type="checkbox" name="supprimer_photo" id="supprimer_photo" value="1">
                                <label class="form-check-label" for="supprimer_photo">Supprimer la photo actuelle</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-4 form-group"><label class="form-label">Nom *</label><input class="form-control" name="nom" value="{{ old('nom', $professeur->nom) }}" required maxlength="50"></div>
                            <div class="col-md-4 form-group"><label class="form-label">Post-nom *</label><input class="form-control" name="postnom" value="{{ old('postnom', $professeur->postnom) }}" required maxlength="50"></div>
                            <div class="col-md-4 form-group"><label class="form-label">Prénom *</label><input class="form-control" name="prenom" value="{{ old('prenom', $professeur->prenom) }}" required maxlength="50"></div>
                            <div class="col-md-4 form-group"><label class="form-label">Sexe *</label><select class="form-control" name="sexe" required><option value="">Choisir</option><option value="M" @selected(old('sexe', $professeur->sexe) === 'M')>Masculin</option><option value="F" @selected(old('sexe', $professeur->sexe) === 'F')>Féminin</option></select></div>
                            <div class="col-md-4 form-group"><label class="form-label">Spécialité *</label><input class="form-control" name="specialite" value="{{ old('specialite', $professeur->specialite) }}" required maxlength="100"></div>
                            <div class="col-md-4 form-group"><label class="form-label">Téléphone *</label><input class="form-control" name="telephone" value="{{ old('telephone', $professeur->telephone) }}" required maxlength="20"></div>
                            <div class="col-md-6 form-group"><label class="form-label">E-mail *</label><input class="form-control" type="email" name="email" value="{{ old('email', $professeur->email) }}" required maxlength="100"></div>
                            <div class="col-md-6 form-group"><label class="form-label">Statut *</label><select class="form-control" name="statut" required><option value="actif" @selected(old('statut', $professeur->statut) === 'actif')>Actif</option><option value="inactif" @selected(old('statut', $professeur->statut) === 'inactif')>Inactif</option></select></div>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer les modifications</button>
                        <a class="btn btn-secondary" href="{{ route('admin.professeurs.show', $professeur) }}">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
