<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier mon profil</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { background: #f8f9fa; }
        .profile-header { background: #0d0c0f; color: #fff; }
        .profile-content { max-width: 960px; margin: 0 auto; }
        .form-label { font-weight: 600; font-size: 13px; }
    </style>
</head>
<body>
    <header class="profile-header p-3 d-flex align-items-center justify-content-between">
        <a class="text-white" href="{{ route('profile.show') }}"><i class="fa fa-arrow-left"></i> Retour à mon profil</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Déconnexion</button></form>
    </header>
    @include('partials.impersonation-banner')
    <main class="container py-4 profile-content">
        <div class="app-title"><div><h1><i class="fa fa-pencil"></i> Modifier mon profil</h1><p>Mettez à jour vos informations et vos coordonnées.</p></div></div>
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="tile">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <h2 class="h4">Informations personnelles</h2>
                <div class="form-group"><label class="form-label" for="photo">Photo de profil</label><input class="form-control-file" id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png"><small class="form-text text-muted">JPG ou PNG, 2 Mo maximum.</small></div>
                <div class="row">
                    <div class="col-md-4 form-group"><label class="form-label" for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="{{ old('nom', $user->nom) }}" maxlength="50"></div>
                    <div class="col-md-4 form-group"><label class="form-label" for="postnom">Post-nom</label><input class="form-control" id="postnom" name="postnom" value="{{ old('postnom', $user->postnom) }}" maxlength="50"></div>
                    <div class="col-md-4 form-group"><label class="form-label" for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="{{ old('prenom', $user->prenom) }}" maxlength="50"></div>
                    <div class="col-md-6 form-group"><label class="form-label" for="date_naissance">Date de naissance</label><input class="form-control" id="date_naissance" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance', $user->date_naissance?->format('Y-m-d')) }}"></div>
                    <div class="col-md-6 form-group"><label class="form-label" for="telephone">Téléphone</label><input class="form-control" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}" maxlength="20"></div>
                    <div class="col-12 form-group"><label class="form-label" for="adresse">Adresse</label><textarea class="form-control" id="adresse" name="adresse" rows="3" maxlength="500">{{ old('adresse', $user->adresse) }}</textarea></div>
                </div>
                <h2 class="h4 mt-3">Informations de connexion</h2>
                <div class="row">
                    <div class="col-md-6 form-group"><label class="form-label" for="username">Nom d'utilisateur *</label><input class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="50"></div>
                    <div class="col-md-6 form-group"><label class="form-label" for="email">Adresse e-mail *</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="100"></div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer</button>
                <a class="btn btn-secondary" href="{{ route('profile.show') }}">Annuler</a>
            </form>
        </section>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
