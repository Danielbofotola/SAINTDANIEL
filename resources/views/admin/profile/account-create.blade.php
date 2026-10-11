<!DOCTYPE html>
<html lang="fr">

<head>
   <title>Gestion Scolaire - Créer un compte</title>
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
            
            <ul class="top-nav lft-nav">
               <li>
                  <a href="#!" class="drop icon-circle txt-white">
                     <i class="ti-file"></i><span> Documents Administratifs</span>
                  </a>
               </li>               
               <li class="dropdown">
                  <a href="#!" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                     <span>Rapports </span><i class="icofont icofont-simple-down"></i>
                  </a>
                  <ul class="dropdown-menu settings-menu">
                     <li><a href="#">Rapports Journaliers</a></li>
                     <li><a href="#">Cahiers de texte</a></li>
                     <li><a href="#">Absences Enseignants</a></li>
                     <li><a href="#">Évaluations & Examens</a></li>
                  </ul>
               </li>
            </ul>

            <!-- Navbar Right Menu-->
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
                <li class="treeview">
                    <a class="waves-effect waves-dark" href="{{ route('admin.profile.show') }}">
                        <i class="icon-user"></i><span> Mon Profil</span>
                    </a>
                </li>
                <li class="active treeview">
                    <a class="waves-effect waves-dark" href="{{ route('admin.profile.accounts') }}">
                        <i class="icon-people"></i><span> Tous les comptes</span>
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

                <li class="nav-level">--- Gestion Administrative</li>
                <li class="treeview">
                    <a class="waves-effect waves-dark" href="#!">
                        <i class="icon-people"></i><span> Personnel & Comptes</span><i class="icon-arrow-down"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.classes.index') }}"><i class="icon-arrow-right"></i> Classes et élèves</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.salles.index') }}"><i class="icon-arrow-right"></i> Salles</a></li>
                    </ul>
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
                     <h4><i class="icofont icofont-user-suited text-primary"></i> Créer un nouveau compte</h4>
                     <p class="text-muted">Attribuez un rôle et des identifiants d'accès à un utilisateur de l'établissement.</p>
                  </div>
                  <a href="{{ route('admin.profile.accounts') }}" class="btn btn-secondary waves-effect">
                     <i class="icofont icofont-reply"></i> Retour aux comptes
                  </a>
               </div>
            </div>

            <!-- Profile Action Quick Bar -->
            <div class="row">
               <div class="col-12">
                  <div class="profile-actions-bar d-flex flex-wrap align-items-center">
                     <a href="{{ route('admin.profile.show') }}"><i class="fa fa-user"></i> Mon profil</a>
                     <a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Tous les comptes</a>
                     <a href="{{ route('admin.dashboard') }}"><i class="fa fa-tachometer"></i> Tableau de bord</a>
                  </div>
               </div>
            </div>

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
                        <h5 class="card-header-text"><i class="icofont icofont-id-card"></i> Formulaire de création de compte</h5>
                     </div>
                     <div class="card-block p-0">
                        <form method="POST" action="{{ route('admin.profile.accounts.store') }}">
                           @csrf
                           
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-ui-user"></i> Informations personnelles</h6>
                           <div class="row">
                              <div class="col-md-4 form-group">
                                 <label>Nom</label>
                                 <input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="50">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Post-nom</label>
                                 <input class="form-control" name="postnom" value="{{ old('postnom') }}" maxlength="50">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Prénom</label>
                                 <input class="form-control" name="prenom" value="{{ old('prenom') }}" maxlength="50">
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Téléphone</label>
                                 <input class="form-control" name="telephone" value="{{ old('telephone') }}" maxlength="20">
                              </div>
                           </div>

                           <hr>
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-lock"></i> Accès et rôles système</h6>
                           
                           <div class="row">
                              <div class="col-md-6 form-group">
                                 <label>Nom d'utilisateur <span class="text-danger">*</span></label>
                                 <input class="form-control" name="username" value="{{ old('username') }}" required maxlength="50">
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Adresse e-mail <span class="text-danger">*</span></label>
                                 <input class="form-control" type="email" name="email" value="{{ old('email') }}" required maxlength="100">
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Rôle <span class="text-danger">*</span></label>
                                 <select class="form-control" id="role" name="role" required>
                                    @foreach(['admin'=>'Administrateur','prefetture'=>'Préfecture','titulaire'=>'Titulaire','professeur'=>'Professeur','comptable'=>'Comptable','parent'=>'Parent'] as $value=>$label)
                                       <option value="{{ $value }}" @selected(old('role', 'professeur') === $value)>{{ $label }}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-8 form-group">
                                 <label for="professeur_id">Professeur lié <span id="professeurRequired" class="text-danger">*</span></label>
                                 <select class="form-control" id="professeur_id" name="professeur_id">
                                    <option value="">Sélectionner un professeur</option>
                                    @foreach($professeurs as $professeur)
                                       <option value="{{ $professeur->id }}" @selected(old('professeur_id') == $professeur->id)>
                                          {{ $professeur->nom }} {{ $professeur->postnom }} {{ $professeur->prenom }} - {{ $professeur->matricule }}
                                       </option>
                                    @endforeach
                                 </select>
                                 <small class="text-muted d-block mt-1">Obligatoire pour le rôle Professeur.</small>
                                 @error('professeur_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                 @enderror
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Statut <span class="text-danger">*</span></label>
                                 <select class="form-control" name="statut" required>
                                    <option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option>
                                    <option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option>
                                 </select>
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>PIN de connexion</label>
                                 <input class="form-control" type="text" name="pin_code" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="4 chiffres">
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Mot de passe <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" name="password" minlength="8" required>
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Confirmer le mot de passe <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" name="password_confirmation" minlength="8" required>
                              </div>
                           </div>

                           <div class="mt-3">
                              <button class="btn btn-primary waves-effect waves-light" type="submit">
                                 <i class="icofont icofont-save"></i> Enregistrer et créer le compte
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
      const roleSelect = document.getElementById('role');
      const professorSelect = document.getElementById('professeur_id');
      const requiredLabel = document.getElementById('professeurRequired');
      
      function updateProfessorRequirement() {
          const required = roleSelect.value === 'professeur';
          professorSelect.required = required;
          requiredLabel.classList.toggle('d-none', !required);
      }
      
      roleSelect.addEventListener('change', updateProfessorRequirement);
      updateProfessorRequirement();
   </script>
</body>
</html>