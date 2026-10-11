<!DOCTYPE html>
<html lang="fr">

<head>
   <title>Gestion Scolaire - Profil de l'Administrateur</title>
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
      .detail-row { display:flex; margin-bottom:14px; font-size:14px; }
      .detail-label { width:200px; font-weight:600; color:#1b1b1b; }
      .detail-value { color:#323130; }
      .profile-actions-bar { background: #1b8bf9; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
      .profile-actions-bar a { color: #fff; margin-right: 15px; font-size: 13px; font-weight: 500; text-decoration: none; }
      .profile-actions-bar a:hover { text-decoration: underline; }
      /* Design du modèle fourni : bandeau sombre + barre d’action + navigation de profil */
      .profile-top { background:#0d0c0f; color:#fff; border-radius:4px; }
      .profile-top h5 { color:#fff; }
      .profile-actions { background:#b26111; border-radius:4px; }
      .profile-actions a { color:#fff; padding:8px 12px; font-size:13px; font-weight:500; text-decoration:none; }
      .profile-actions a:hover { color:#fff; background:rgba(255,255,255,.15); }
      .profile-nav { display:flex; flex-wrap:wrap; align-items:center; gap:2px; }
      .profile-nav .nav-link { color:#323130; font-size:14px; padding:10px 16px; border-left:3px solid transparent; }
      .profile-nav .nav-link i { width:22px; color:#0078d4; }
      .profile-nav .nav-link.active, .profile-nav .nav-link:hover { background:#f3f2f1; border-left-color:#0078d4; text-decoration:none; }
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
                  
                  <!-- Notifications Menu -->
                  <li class="dropdown notification-menu">
                     <a href="#!" data-toggle="dropdown" aria-expanded="false" class="dropdown-toggle">
                        <i class="icon-bell"></i>
                        <span class="badge badge-danger header-badge">3</span>
                     </a>
                     <ul class="dropdown-menu">
                        <li class="not-head">Vous avez <b class="text-primary">3</b> nouvelles notifications.</li>
                        <li class="bell-notification">
                           <a href="javascript:;" class="media">
                              <span class="media-left media-icon"><i class="ti-user text-warning"></i></span>
                              <div class="media-body"><span class="block">Demande de congé d'un enseignant</span><span class="text-muted block-time">Il y a 10 min</span></div>
                           </a>
                        </li>
                        <li class="bell-notification">
                           <a href="javascript:;" class="media">
                              <span class="media-left media-icon"><i class="ti-alert text-danger"></i></span>
                              <div class="media-body"><span class="block">Rapport de discipline soumis</span><span class="text-muted block-time">Il y a 1 heure</span></div>
                           </a>
                        </li>
                     </ul>
                  </li>

                  <!-- User Menu-->
                  <li class="dropdown">
                     <a href="#!" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                        <span>
                           @if ($user->photo)
                              <img class="img-circle" src="{{ asset('storage/'.$user->photo) }}" style="width:40px; height:40px; object-fit:cover;" alt="Photo">
                           @else
                              <span class="mini-avatar">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                           @endif
                        </span>
                        <span>{{ strtoupper(trim($user->prenom ?: $user->username)) }} <i class="icofont icofont-simple-down"></i></span>
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
                <li class="active treeview">
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
                <li class="treeview">
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
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.professeurs.index') }}">
                           <i class="icon-settings"></i><span> Professeur & attachement</span>
                     </a>
                  </li>
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.emplois-du-temps.index') }}">
                           <i class="icon-settings"></i><span> Emplois du Temps</span>
                     </a>
                  </li>
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.bulletins.index') }}">
                           <i class="icon-settings"></i><span>Validation Finale des 
                              <p>Bulletins Trimestriels</p>
                           </span>
                     </a>
                  </li>
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.modules.index') }}">
                           <i class="icon-settings"></i><span>Commuter les modules 
                              <p>(Saisie
                              Notes, Portail Parents, Délibération) </p>
                           </span>
                     </a>
                  </li>
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.deliberations.index') }}">
                           <i class="icon-chart"></i><span> Délibération &amp; passage de classe</span>
                     </a>
                  </li>
                  <li class="treeview">
                     <a class="waves-effect waves-dark" href="{{ route('admin.messagerie.index') }}">
                           <i class="icon-people"></i><span> Messagerie (modération)</span>
                     </a>
                  </li>

                <li class="nav-level">--- Gestion Administrative</li>
                <li class="treeview">
                    <a class="waves-effect waves-dark" href="#!">
                        <i class="icon-people"></i><span> reglage &  affectation</span><i class="icon-arrow-down"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.classes.index') }}"><i class="icon-arrow-right"></i> Classes et élèves</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.salles.index') }}"><i class="icon-arrow-right"></i> Salles</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.affectations.index') }}"><i class="icon-arrow-right"></i>Gestion de cours</a></li>
                        <li><a class="waves-effect waves-dark" href="#!"><i class="icon-arrow-right"></i>Gestion de Programme</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.periodes.index') }}"><i class="icon-arrow-right"></i>Gestion de periode</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.affectations.index') }}"><i class="icon-arrow-right"></i>Gestion des affectations_cours</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.evaluations.index') }}"><i class="icon-arrow-right"></i>Gestion des evaluations</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.notes.index') }}"><i class="icon-arrow-right"></i>Gestion des notes </a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.bulletins.index') }}"><i class="icon-arrow-right"></i>Gestion des bulletins</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.archives.index') }}"><i class="icon-arrow-right"></i>Gestion des archives_annees</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.paies.index') }}"><i class="icon-arrow-right"></i>Gestion des salaires</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.comptabilite.index') }}"><i class="icon-arrow-right"></i>Comptabilité OHADA</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.statistiques.index') }}"><i class="icon-arrow-right"></i>Statistiques &amp; évolution</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.eleves.index') }}"><i class="icon-arrow-right"></i>Gestion des documents_eleves</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.periodes.index') }}"><i class="icon-arrow-right"></i>Gestion des periodes</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a class="waves-effect waves-dark" href="#!">
                        <i class="icon-people"></i><span> paiement &  Caisse</span><i class="icon-arrow-down"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.frais.index') }}"><i class="icon-arrow-right"></i>Type de frais</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.frais.index') }}"><i class="icon-arrow-right"></i> frais_scolaires</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.paiements.index') }}"><i class="icon-arrow-right"></i> paiements</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.depenses.index') }}"><i class="icon-arrow-right"></i> categories_depenses</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.depenses.index') }}"><i class="icon-arrow-right"></i> depenses</a></li>
                        <li><a class="waves-effect waves-dark" href="{{ route('admin.archives.index') }}"><i class="icon-arrow-right"></i> archives_annees</a></li>
                    </ul>
                </li>
            </ul>
         </section>
      </aside>

      <!-- Main Content -->
      <div class="content-wrapper">
         <div class="container-fluid">
            
            <div class="row">
               <div class="main-header">
                  <h4>Profil Administrateur : <b>{{ strtoupper(trim(($user->nom.' '.$user->postnom.' '.$user->prenom) ?: $user->username)) }}</b></h4>
                  <p class="text-muted">Consultez et mettez à jour vos informations personnelles, la sécurité et la gestion des accès.</p>
               </div>
            </div>

            <!-- Profile Action Quick Bar -->
             <!-- Bandeau de profil -->
             <div class="row">
                <div class="col-12">
                   <div class="profile-top p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap">
                      <div class="d-flex align-items-center">
                         @if ($user->photo)
                            <img class="avatar mr-3" src="{{ asset('storage/'.$user->photo) }}" alt="Photo de profil">
                         @else
                            <span class="avatar mr-3">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                         @endif
                         <div>
                            <h5 class="mb-0">{{ strtoupper(trim(($user->nom.' '.$user->prenom) ?: $user->username)) }}</h5>
                            <small class="text-white-50">Utilisateur / Administrateur</small>
                         </div>
                      </div>
                      <form method="POST" action="{{ route('logout') }}">
                         @csrf
                         <button class="btn btn-light btn-sm" type="submit"><i class="fa fa-sign-out"></i> Déconnexion</button>
                      </form>
                   </div>
                </div>
             </div>

             <!-- Navigation de profil -->
             <div class="row">
                <div class="col-12">
                   <div class="card profile-nav p-2 mb-3">
                      <a class="nav-link active" href="{{ route('admin.profile.show') }}"><i class="fa fa-desktop"></i> Vue d'ensemble</a>
                      <a class="nav-link" href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Tous les comptes</a>
                      <a class="nav-link" href="{{ route('admin.profile.online') }}"><i class="fa fa-plug"></i> Utilisateurs en ligne</a>
                      <a class="nav-link" href="{{ route('admin.profile.statistics') }}"><i class="fa fa-bar-chart"></i> Statistiques</a>
                      <a class="nav-link" href="{{ route('admin.profile.settings') }}"><i class="fa fa-cog"></i> Paramètres de l'établissement</a>
                      <a class="nav-link" href="#password"><i class="fa fa-key"></i> Sécurité du compte</a>
                      <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                      <a class="nav-link" href="{{ route('admin.classes.index') }}"><i class="fa fa-users"></i> Classes et élèves</a>
                      <a class="nav-link" href="{{ route('admin.salles.index') }}"><i class="fa fa-university"></i> Salles</a>
                   </div>
                </div>
             </div>
            <div class="row">
               <div class="col-12">
                  <div class="profile-actions d-flex flex-wrap align-items-center">
                     <a href="{{ route('admin.profile.edit') }}"><i class="fa fa-user"></i> Modifier les propriétés</a>
                     <a href="{{ route('admin.profile.settings') }}"><i class="fa fa-cog"></i> Paramètres de l’établissement</a>
                     <a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Gérer les comptes</a>
                     <a href="{{ route('admin.profile.statistics') }}"><i class="fa fa-bar-chart"></i> Statistiques</a>
                     <a href="{{ route('admin.profile.show') }}"><i class="fa fa-refresh"></i> Actualiser</a>
                     <a href="#password"><i class="fa fa-key"></i> Réinitialiser le mot de passe</a>
                  </div>
               </div>
            </div>

            @if (session('success'))
               <div class="alert alert-success border-0">{{ session('success') }}</div>
            @endif

            <!-- User Info & Accounts Row -->
            <div class="row">
               <!-- Details Profile -->
               <div class="col-lg-6">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-3">
                        <h5 class="card-header-text">Informations de l'administrateur</h5>
                     </div>
                     <div class="card-block p-0">
                        <div class="d-flex align-items-start mb-4">
                           @if ($user->photo)
                              <img class="avatar mr-4" src="{{ asset('storage/'.$user->photo) }}" alt="Photo de profil">
                           @else
                              <span class="avatar mr-4">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                           @endif
                           <div>
                              <h4 class="m-0">{{ strtoupper(trim(($user->nom.' '.$user->postnom.' '.$user->prenom) ?: $user->username)) }}</h4>
                              <span class="label label-danger badge-lg mt-2 d-inline-block">Administrateur</span>
                           </div>
                        </div>

                        <div class="detail-row"><div class="detail-label">Nom d'utilisateur</div><div class="detail-value">{{ $user->username }}</div></div>
                        <div class="detail-row"><div class="detail-label">E-mail</div><div class="detail-value">{{ $user->email }}</div></div>
                        <div class="detail-row"><div class="detail-label">Téléphone</div><div class="detail-value">{{ $user->telephone ?: 'Non renseigné' }}</div></div>
                        <div class="detail-row"><div class="detail-label">Date de naissance</div><div class="detail-value">{{ $user->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</div></div>
                        <div class="detail-row"><div class="detail-label">Adresse</div><div class="detail-value">{{ $user->adresse ?: 'Non renseignée' }}</div></div>
                        <div class="detail-row"><div class="detail-label">Statut</div><div class="detail-value"><span class="label label-success">{{ ucfirst($user->statut) }}</span></div></div>
                        <div class="detail-row"><div class="detail-label">Dernière connexion</div><div class="detail-value">{{ $user->last_login?->format('d/m/Y H:i') ?? 'Pas encore connecté' }}</div></div>
                     </div>
                  </div>
               </div>

               <!-- Linked Accounts & Roles -->
               <div class="col-lg-6">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-header-text m-0">Utilisateurs et rôles</h5>
                        <span class="label label-primary">{{ $users->count() }} / 5</span>
                     </div>
                     <div class="card-block p-0">
                        <p class="text-muted">Profils, rôles et professeurs liés aux comptes de l'établissement.</p>
                        
                        @forelse ($users as $account)
                           @php($accountName = trim(($account->nom.' '.$account->postnom.' '.$account->prenom)) ?: $account->username)
                           <div class="d-flex align-items-center border-bottom py-2">
                              @if ($account->photo)
                                 <img class="mini-avatar mr-2" src="{{ asset('storage/'.$account->photo) }}" alt="Photo">
                              @else
                                 <span class="mini-avatar mr-2">{{ strtoupper(substr($accountName, 0, 1)) }}</span>
                              @endif
                              <div class="flex-grow-1">
                                 <strong>{{ $accountName }}</strong><br>
                                 <small class="text-muted">{{ $account->email }}</small>
                              </div>
                              <div class="text-right">
                                 <span class="label label-info">{{ ucfirst($account->role) }}</span><br>
                                 <small class="text-muted">{{ $account->professeur ? trim($account->professeur->nom.' '.$account->professeur->postnom.' '.$account->professeur->prenom) : 'Aucun professeur lié' }}</small>
                              </div>
                           </div>
                        @empty
                           <p class="text-muted mb-0">Aucun compte utilisateur.</p>
                        @endforelse

                        <div class="m-t-20">
                           <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.profile.accounts') }}">
                              <i class="icofont icofont-list"></i> Voir tous les comptes
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Reset Password Section -->
            <div class="row" id="password">
               <div class="col-xl-12">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-3">
                        <h5 class="card-header-text"><i class="icofont icofont-key"></i> Sécurité du compte</h5>
                     </div>
                     <div class="card-block p-0">
                        <form method="POST" action="{{ route('admin.profile.password') }}">
                           @csrf
                           @method('PATCH')
                           <div class="row">
                              <div class="col-md-4 form-group">
                                 <label>Mot de passe actuel</label>
                                 <input class="form-control" type="password" name="current_password" required>
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Nouveau mot de passe</label>
                                 <input class="form-control" type="password" name="password" minlength="8" required>
                              </div>
                              <div class="col-md-4 form-group">
                                 <label>Confirmer le mot de passe</label>
                                 <input class="form-control" type="password" name="password_confirmation" minlength="8" required>
                              </div>
                           </div>
                           <button class="btn btn-warning waves-effect waves-light mt-2" type="submit">
                              <i class="icofont icofont-save"></i> Modifier le mot de passe
                           </button>
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

</body>
</html>