<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un professeur</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { background:#f8f9fa; }
        .profile-top { background:#0d0c0f; color:#fff; }
        .profile-actions { background:#b26111; }
        .profile-actions a { color:#fff; padding:8px 12px; }
        .profile-actions a:hover { color:#fff; background:rgba(255,255,255,.15); text-decoration:none; }
        .profile-sidebar { min-height:calc(100vh - 104px); background:#fff; border-right:1px solid #e0e0e0; }
        .profile-sidebar .nav-link { color:#323130; font-size:14px; padding:10px 16px; border-left:3px solid transparent; }
        .profile-sidebar .nav-link i { width:24px; color:#0078d4; }
        .profile-sidebar .nav-link.active, .profile-sidebar .nav-link:hover { background:#f3f2f1; border-left-color:#0078d4; }
        .avatar { width:52px; height:52px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:22px; }
        .profile-card { border:0; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .form-label { font-weight:600; font-size:13px; }
    </style>
</head>
<body>
    <div class="profile-top p-3 d-flex align-items-center justify-content-between"><div class="d-flex align-items-center"><span class="avatar mr-3"><i class="fa fa-user-md"></i></span><div><h5 class="mb-0">NOUVEAU PROFESSEUR</h5><small class="text-muted">Création par l'administrateur</small></div></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Déconnexion</button></form></div>
    <div class="profile-actions d-flex align-items-center"><a href="{{ route('admin.profile.show') }}"><i class="fa fa-user"></i> Mon profil</a><a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Comptes</a><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a></div>
    <div class="d-flex"><aside class="profile-sidebar p-3" style="width:260px"><a class="nav-link" href="{{ route('admin.profile.show') }}"><i class="fa fa-desktop"></i> Vue d'ensemble</a><a class="nav-link active" href="{{ route('admin.professeurs.create') }}"><i class="fa fa-user-md"></i> Créer un professeur</a><a class="nav-link" href="{{ route('admin.profile.accounts.create') }}"><i class="fa fa-user-plus"></i> Créer un compte</a><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a><div class="font-weight-bold small mt-4 mb-2 px-3">Gérer</div><a class="nav-link" href="{{ route('admin.classes.index') }}"><i class="fa fa-graduation-cap"></i> Classes et élèves</a><a class="nav-link" href="{{ route('admin.salles.index') }}"><i class="fa fa-university"></i> Salles</a></aside>
        <main class="flex-grow-1 p-4"><div class="app-title"><div><h1><i class="fa fa-user-md"></i> Créer un professeur</h1><p>Enregistrez le profil du professeur avant de lui attribuer un compte.</p></div><a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Retour</a></div>
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="card profile-card"><div class="card-body"><form method="POST" action="{{ route('admin.professeurs.store') }}" enctype="multipart/form-data">@csrf
                <h4 class="card-title">Identité professionnelle</h4><div class="alert alert-info"><i class="fa fa-info-circle"></i> Le matricule sera généré automatiquement après l'enregistrement.</div><div class="row"><div class="col-md-4 form-group"><label class="form-label">Nom *</label><input class="form-control" name="nom" value="{{ old('nom') }}" required maxlength="50"></div><div class="col-md-4 form-group"><label class="form-label">Post-nom *</label><input class="form-control" name="postnom" value="{{ old('postnom') }}" required maxlength="50"></div><div class="col-md-4 form-group"><label class="form-label">Prénom *</label><input class="form-control" name="prenom" value="{{ old('prenom') }}" required maxlength="50"></div><div class="col-md-4 form-group"><label class="form-label">Sexe *</label><select class="form-control" name="sexe" required><option value="">Choisir</option><option value="M" @selected(old('sexe') === 'M')>Masculin</option><option value="F" @selected(old('sexe') === 'F')>Féminin</option></select></div><div class="col-md-4 form-group"><label class="form-label">Spécialité *</label><input class="form-control" name="specialite" value="{{ old('specialite') }}" required maxlength="100" placeholder="Ex. Mathématiques"></div><div class="col-md-4 form-group"><label class="form-label">Téléphone *</label><input class="form-control" name="telephone" value="{{ old('telephone') }}" required maxlength="20"></div><div class="col-md-6 form-group"><label class="form-label">E-mail *</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required maxlength="100"></div><div class="col-md-6 form-group"><label class="form-label">Photo</label><input class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png"><small class="text-muted">JPG, JPEG ou PNG, 2 Mo maximum.</small></div><div class="col-md-6 form-group"><label class="form-label">Statut *</label><select class="form-control" name="statut" required><option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option><option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option></select></div></div>
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer le professeur</button>
            </form></div></div>
        </main>
    </div>
</body>
</html>