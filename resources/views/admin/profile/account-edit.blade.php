<!DOCTYPE html>
<html lang="fr">

<head>
   <title>Gestion Scolaire - Modifier un compte</title>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">

   <!-- Favicon icon -->
   <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
   <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">

   <!-- Google font-->
   <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,500,700" rel="stylesheet">

   <!-- Icons -->
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/themify-icons/themify-icons.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/icofont/css/icofont.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/simple-line-icons/css/simple-line-icons.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">

   <!-- Required Framework -->
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">

   <!-- Style.css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">

   <style>
      .avatar { width:82px; height:82px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:30px; font-weight:600; object-fit:cover; }
      .mini-avatar { width:38px; height:38px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; object-fit:cover; }
      .profile-actions-bar { background: #1b8bf9; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
      .profile-actions-bar a { color: #fff; margin-right: 15px; font-size: 13px; font-weight: 500; text-decoration: none; }
      .profile-actions-bar a:hover { text-decoration: underline; }
   </style>
</head>

<body class="sidebar-mini fixed">
   <div class="loader-bg">
      <div class="loader-bar"></div>
   </div>
   <div class="wrapper">

      <!-- Navbar Top -->
      <header class="main-header-top hidden-print">
         <a href="{{ route('admin.dashboard') }}" class="logo">
            <img class="img-fluid able-logo" src="{{ asset('assets/images/logo.png') }}" alt="Logo École">
         </a>
         <nav class="navbar navbar-static-top">
            <a href="#!" data-toggle="offcanvas" class="sidebar-toggle"></a>
            <div class="navbar-custom-menu f-right">
               <ul class="top-nav">
                  <li class="dropdown">
                     <a href="#!" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                        <span>
                           @if (auth()->user()->photo)
                              <img class="img-circle" src="{{ asset('storage/'.auth()->user()->photo) }}" style="width:40px; height:40px; object-fit:cover;" alt="Photo">
                           @else
                              <span class="mini-avatar">{{ strtoupper(substr(auth()->user()->prenom ?: auth()->user()->username, 0, 1)) }}</span>
                           @endif
                        </span>
                        <span>{{ strtoupper(trim(auth()->user()->prenom ?: auth()->user()->username)) }} <i class="icofont icofont-simple-down"></i></span>
                     </a>
                     <ul class="dropdown-menu settings-menu">
                        <li><a href="{{ route('admin.profile.settings') }}"><i class="icon-settings"></i> Paramètres</a></li>
                        <li><a href="{{ route('admin.profile.show') }}"><i class="icon-user"></i> Mon Profil</a></li>
                        <li>
                           <a href="#!" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                              <i class="icon-logout"></i> Déconnexion
                           </a>
                           <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>
                        </li>
                     </ul>
                  </li>
               </ul>
            </div>
         </nav>
      </header>

      <!-- Side-Nav Navigation Menu -->
      <aside class="main-sidebar hidden-print">
         <section class="sidebar" id="sidebar-scroll">
            <ul class="sidebar-menu">
               <li class="nav-level">--- Général</li>
               <li class="treeview">
                  <a class="waves-effect waves-dark" href="{{ route('admin.dashboard') }}">
                     <i class="icon-speedometer"></i><span> Tableau de bord</span>
                  </a>
               </li>

               <li class="nav-level">--- Profil & Configuration</li>
               <li class="active treeview">
                  <a class="waves-effect waves-dark" href="{{ route('admin.profile.accounts') }}">
                     <i class="icon-people"></i><span> Tous les comptes</span>
                  </a>
               </li>
               <li class="treeview">
                  <a class="waves-effect waves-dark" href="{{ route('admin.profile.show') }}">
                     <i class="icon-user"></i><span> Mon Profil</span>
                  </a>
               </li>
               <li class="treeview">
                  <a class="waves-effect waves-dark" href="{{ route('admin.profile.statistics') }}">
                     <i class="icon-chart"></i><span> Statistiques</span>
                  </a>
               </li>
               <li class="treeview">
                  <a class="waves-effect waves-dark" href="{{ route('admin.profile.settings') }}">
                     <i class="icon-settings"></i><span> Paramètres Établissement</span>
                  </a>
               </li>
            </ul>
         </section>
      </aside>

      <!-- Main Content -->
      <div class="content-wrapper">
         <div class="container-fluid">

            <div class="row">
               <div class="main-header d-flex justify-content-between align-items-center">
                  <div>
                     <h4><i class="icofont icofont-user-alt-3 text-primary"></i> Modifier un compte utilisateur</h4>
                     <p class="text-muted">Profil, rôle, mot de passe et code PIN de {{ trim($user->prenom.' '.$user->nom) ?: $user->username }}.</p>
                  </div>
                  <a href="{{ route('admin.profile.accounts') }}" class="btn btn-secondary waves-effect">
                     <i class="icofont icofont-reply"></i> Retour
                  </a>
               </div>
            </div>

            <!-- Profile Action Quick Bar -->
            <div class="row">
               <div class="col-12">
                  <div class="profile-actions-bar d-flex flex-wrap align-items-center">
                     <a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Tous les comptes</a>
                     <a href="{{ route('admin.profile.show') }}"><i class="fa fa-user"></i> Mon profil</a>
                     <a href="{{ route('admin.dashboard') }}"><i class="fa fa-tachometer"></i> Tableau de bord</a>
                  </div>
               </div>
            </div>

            @if (session('success'))
               <div class="alert alert-success border-0">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
               <div class="alert alert-danger border-0">
                  <ul class="mb-0 pl-3">
                     @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                     @endforeach
                  </ul>
               </div>
            @endif

            <div class="row">
               <div class="col-lg-12">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-4">
                        <h5 class="card-header-text"><i class="icofont icofont-edit"></i> Compte de {{ $user->username }}</h5>
                     </div>
                     <div class="card-block p-0">
                        <form method="POST" action="{{ route('admin.profile.accounts.update', $user) }}" enctype="multipart/form-data">
                           @csrf
                           @method('PUT')

                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-image"></i> Photo de profil</h6>
                           <div class="row align-items-center mb-4">
                              <div class="col-md-2 text-center">
                                 @if ($user->photo)
                                    <img id="preview" class="avatar" src="{{ asset('storage/'.$user->photo) }}" alt="Photo de profil">
                                 @else
                                    <span id="placeholder" class="avatar">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                                    <img id="preview" class="avatar d-none" alt="Aperçu de la photo">
                                 @endif
                              </div>
                              <div class="col-md-10">
                                 <label for="photo">Sélectionner une nouvelle photo</label>
                                 <input class="form-control" id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png">
                                 <small class="text-muted d-block mt-1">Formats autorisés : JPG ou PNG. Taille max : 2 Mo.</small>
                              </div>
                           </div>

                           <hr>
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-id-card"></i> Informations personnelles</h6>
                           <div class="row">
                              <div class="col-md-4 form-group">
                                 <label>Nom</label>
                                 <input class="form-control" name="nom" value="{{ old('nom', $user->nom) }}" maxlength="50">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Post-nom</label>
                                 <input class="form-control" name="postnom" value="{{ old('postnom', $user->postnom) }}" maxlength="50">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Prénom</label>
                                 <input class="form-control" name="prenom" value="{{ old('prenom', $user->prenom) }}" maxlength="50">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Date de naissance</label>
                                 <input class="form-control" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance', $user->date_naissance?->format('Y-m-d')) }}">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Téléphone</label>
                                 <input class="form-control" name="telephone" value="{{ old('telephone', $user->telephone) }}" maxlength="20">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Adresse</label>
                                 <input class="form-control" name="adresse" value="{{ old('adresse', $user->adresse) }}" maxlength="500">
                              </div>
                           </div>

                           <hr>
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-lock"></i> Accès et rôle système</h6>
                           <div class="row">
                              <div class="col-md-6 form-group">
                                 <label>Nom d'utilisateur <span class="text-danger">*</span></label>
                                 <input class="form-control" name="username" value="{{ old('username', $user->username) }}" required maxlength="50">
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Adresse e-mail <span class="text-danger">*</span></label>
                                 <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="100">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Rôle <span class="text-danger">*</span></label>
                                 <select class="form-control" name="role" required>
                                    @foreach(['admin'=>'Administrateur','prefetture'=>'Préfecture','titulaire'=>'Titulaire','professeur'=>'Professeur','comptable'=>'Comptable','parent'=>'Parent'] as $value=>$label)
                                       <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-4 form-group">
                                 <label for="professeur_id">Professeur lié</label>
                                 <select class="form-control" id="professeur_id" name="professeur_id">
                                    <option value="">Sélectionner un professeur</option>
                                    @foreach ($professeurs as $professeur)
                                       <option value="{{ $professeur->id }}" @selected(old('professeur_id', $user->professeur_id) == $professeur->id)>
                                          {{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }} - {{ $professeur->matricule }}
                                       </option>
                                    @endforeach
                                 </select>
                                 <small class="text-muted d-block mt-1">Obligatoire pour le rôle Professeur.</small>
                                 @error('professeur_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                 @enderror
                              </div>
                              <div class="col-md-4 form-group" id="blocEnfants" @if (old('role', $user->role) !== 'parent') style="display:none" @endif>
                                 <label for="enfants">Enfants rattachés</label>
                                 <select class="form-control" id="enfants" name="enfants[]" multiple size="6">
                                    @foreach ($eleves as $eleve)
                                       <option value="{{ $eleve->id }}" @selected(in_array($eleve->id, old('enfants', $enfantsSelectionnes)))>
                                          {{ trim($eleve->nom.' '.$eleve->postnom.' '.$eleve->prenom) }} — {{ $eleve->matricule }}
                                       </option>
                                    @endforeach
                                 </select>
                                 <small class="text-muted d-block mt-1">Un compte parent peut suivre plusieurs enfants (Ctrl/Cmd pour en sélectionner plusieurs).</small>
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Statut <span class="text-danger">*</span></label>
                                 <select class="form-control" name="statut" required>
                                    <option value="actif" @selected(old('statut', $user->statut) === 'actif')>Actif</option>
                                    <option value="inactif" @selected(old('statut', $user->statut) === 'inactif')>Inactif</option>
                                 </select>
                              </div>
                           </div>

                           <hr>
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-key"></i> Identifiants de connexion</h6>
                           <p class="text-muted small mb-2">Le mot de passe et le code PIN de ce compte se réinitialisent sur une page dédiée.</p>
                           <a href="{{ route('admin.profile.accounts.password.edit', $user) }}" class="btn btn-outline-primary waves-effect">
                              <i class="icofont icofont-key"></i> Réinitialiser le mot de passe / PIN
                           </a>

                           <div class="mt-3">
                              <a href="{{ route('admin.profile.accounts') }}" class="btn btn-secondary waves-effect">Annuler</a>
                              <button class="btn btn-primary waves-effect waves-light" type="submit">
                                 <i class="icofont icofont-save"></i> Enregistrer les modifications
                              </button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>

         </div>
      </div>
   </div>

   <!-- Required Scripts -->
   <script src="{{ asset('assets/plugins/Jquery/dist/jquery.min.js') }}"></script>
   <script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
   <script src="{{ asset('assets/plugins/tether/dist/js/tether.min.js') }}"></script>
   <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
   <script src="{{ asset('assets/plugins/jquery-slimscroll/jquery.slimscroll.js') }}"></script>

   <!-- Custom Scripts -->
   <script type="text/javascript" src="{{ asset('assets/js/main.min.js') }}"></script>
   <script src="{{ asset('assets/js/menu.min.js') }}"></script>

   <script>
      // Le rattachement des enfants ne concerne que les comptes parents.
      (function () {
         var role = document.querySelector('select[name="role"]');
         var bloc = document.getElementById('blocEnfants');
         if (!role || !bloc) return;
         role.addEventListener('change', function () {
            bloc.style.display = role.value === 'parent' ? '' : 'none';
         });
      })();

      var photoInput = document.getElementById('photo');
      if (photoInput) {
         photoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;
            const preview = document.getElementById('preview');
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            document.getElementById('placeholder')?.classList.add('d-none');
         });
      }
   </script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
