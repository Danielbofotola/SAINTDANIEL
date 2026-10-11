<!DOCTYPE html>
<html lang="fr">

<head>
   <title>Gestion Scolaire - Réinitialiser les identifiants</title>
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
      .account-chip { display:flex; align-items:center; gap:12px; padding:14px 16px; background:#f7f9fb; border:1px solid #e3e9ef; border-radius:6px; }
      .account-chip .account-name { font-weight:600; color:#263238; }
      .account-chip .account-meta { color:#78909c; font-size:12px; }
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
                     <h4><i class="icofont icofont-key text-primary"></i> Réinitialiser les identifiants</h4>
                     <p class="text-muted">Définissez un nouveau mot de passe et, si besoin, un nouveau code PIN pour ce compte.</p>
                  </div>
                  <a href="{{ route('admin.profile.accounts') }}" class="btn btn-secondary waves-effect">
                     <i class="icofont icofont-reply"></i> Retour aux comptes
                  </a>
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
               <div class="col-lg-8">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-4">
                        <h5 class="card-header-text"><i class="icofont icofont-ui-user"></i> Compte concerné</h5>
                     </div>
                     <div class="card-block p-0">

                        <div class="account-chip mb-4">
                           @if ($user->photo)
                              <img class="mini-avatar" src="{{ asset('storage/'.$user->photo) }}" alt="Photo">
                           @else
                              <span class="mini-avatar">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                           @endif
                           <div>
                              <div class="account-name">{{ trim($user->prenom.' '.$user->nom) ?: $user->username }}</div>
                              <div class="account-meta">{{ $user->username }} · {{ $user->email }} · rôle {{ ucfirst($user->role) }}</div>
                              <div class="account-meta">
                                 @if ($user->pin_code) Un code PIN est actuellement défini. @else Aucun code PIN défini. @endif
                                 · statut {{ $user->statut }}
                              </div>
                           </div>
                        </div>

                        <div class="alert alert-warning border-0">
                           <i class="icofont icofont-warning-alt"></i>
                           Le mot de passe actuel de ce compte n'est ni affiché ni récupérable : il sera simplement <strong>remplacé</strong>.
                           Prévenez l'utilisateur concerné de communiquer les nouvelles informations de connexion.
                        </div>

                        <form method="POST" action="{{ route('admin.profile.accounts.password.update', $user) }}">
                           @csrf
                           @method('PATCH')

                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-lock"></i> Nouveau mot de passe</h6>
                           <div class="row">
                              <div class="col-md-6 form-group">
                                 <label>Mot de passe <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" required>
                                 <small class="text-muted d-block mt-1">Au moins 8 caractères.</small>
                                 @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                              </div>
                              <div class="col-md-6 form-group">
                                 <label>Confirmer le mot de passe <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                              </div>
                           </div>

                           <hr>
                           <h6 class="text-primary font-weight-bold mb-3"><i class="icofont icofont-ui-password"></i> Code PIN de connexion rapide</h6>
                           <div class="row">
                              <div class="col-md-6 form-group">
                                 <label>Nouveau code PIN</label>
                                 <input class="form-control" type="text" name="pin_code" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="4 chiffres (facultatif)">
                                 <small class="text-muted d-block mt-1">Laissez vide pour ne pas changer le code PIN actuel.</small>
                                 @error('pin_code')<span class="text-danger small">{{ $message }}</span>@enderror
                              </div>
                              <div class="col-md-6 form-group d-flex align-items-end">
                                 @if ($user->pin_code)
                                    <div class="form-check">
                                       <input class="form-check-input" type="checkbox" value="1" id="remove_pin" name="remove_pin">
                                       <label class="form-check-label" for="remove_pin">Retirer le code PIN de ce compte</label>
                                    </div>
                                 @endif
                              </div>
                           </div>

                           <div class="mt-3">
                              <a href="{{ route('admin.profile.accounts') }}" class="btn btn-secondary waves-effect">Annuler</a>
                              <a href="{{ route('admin.profile.accounts.edit', $user) }}" class="btn btn-default waves-effect">Modifier le profil</a>
                              <button class="btn btn-primary waves-effect waves-light" type="submit">
                                 <i class="icofont icofont-save"></i> Réinitialiser les identifiants
                              </button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>

               <div class="col-lg-4">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-3">
                        <h5 class="card-header-text"><i class="icofont icofont-info-circle"></i> Bon à savoir</h5>
                     </div>
                     <div class="card-block p-0">
                        <ul class="pl-3 mb-0 text-muted small">
                           <li>Le remplacement est immédiat : l'utilisateur se connecte avec le nouveau mot de passe dès la prochaine tentative.</li>
                           <li>Le code PIN sert à la connexion rapide ; il doit être unique pour rester utilisable.</li>
                           <li>Pour changer votre <strong>propre</strong> mot de passe, passez par <a href="{{ route('admin.profile.edit') }}">votre profil</a>.</li>
                        </ul>
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
</body>
</html>
