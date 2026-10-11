<!DOCTYPE html>
<html lang="fr">

<head>
   <title>Gestion Scolaire - Tous les comptes utilisateurs</title>
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

   <!-- DataTables -->
   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

   <!-- Style.css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">

   <style>
      .mini-avatar { width:32px; height:32px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; object-fit:cover; }
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
                              <span class="mini-avatar" style="width:40px; height:40px;">{{ strtoupper(substr(auth()->user()->prenom ?: auth()->user()->username, 0, 1)) }}</span>
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
                     <h4><i class="icofont icofont-users-social text-primary"></i> Tous les comptes utilisateurs</h4>
                     <p class="text-muted">Consultez, ouvrez et gérez l'ensemble des comptes de l'établissement.</p>
                  </div>
                  <div>
                     <span class="label label-primary mr-2">{{ $users->count() }} compte(s)</span>
                     <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.profile.accounts.create') }}">
                        <i class="icofont icofont-user-plus"></i> Créer un compte
                     </a>
                  </div>
               </div>
            </div>

            <!-- Profile Action Quick Bar -->
            <div class="row">
               <div class="col-12">
                  <div class="profile-actions-bar d-flex flex-wrap align-items-center">
                     <a href="{{ route('admin.profile.show') }}"><i class="fa fa-user"></i> Mon profil</a>
                     <a href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Tous les comptes</a>
                     <a href="{{ route('admin.profile.edit') }}"><i class="fa fa-pencil"></i> Modifier mon profil</a>
                     <a href="{{ route('admin.dashboard') }}"><i class="fa fa-tachometer"></i> Tableau de bord</a>
                  </div>
               </div>
            </div>

            @if (session('success'))
               <div class="alert alert-success border-0">{{ session('success') }}</div>
            @endif

            @if (session('error'))
               <div class="alert alert-danger border-0">{{ session('error') }}</div>
            @endif

            <div class="row">
               <div class="col-lg-12">
                  <div class="card p-20">
                     <div class="card-header border-0 p-0 mb-3">
                        <h5 class="card-header-text"><i class="icofont icofont-table"></i> Liste des utilisateurs</h5>
                     </div>
                     <div class="card-block p-0">
                        <div class="table-responsive">
                           <table id="accountsTable" class="table table-hover table-bordered mb-0">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>Utilisateur</th>
                                    <th>E-mail</th>
                                    <th>Rôle</th>
                                    <th>Dernière connexion</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @forelse ($users as $user)
                                    <tr>
                                       <td>{{ $loop->iteration }}</td>
                                       <td>
                                          <div class="d-flex align-items-center">
                                             @if ($user->photo)
                                                <img src="{{ asset('storage/'.$user->photo) }}" alt="Photo" width="32" height="32" class="img-circle mr-2" style="object-fit:cover">
                                             @else
                                                <span class="mini-avatar mr-2">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                                             @endif
                                             <strong>{{ $user->username }}</strong>
                                             @if ($user->is(auth()->user())) 
                                                <span class="label label-info ml-2">Vous</span>
                                             @endif
                                          </div>
                                       </td>
                                       <td>{{ $user->email }}</td>
                                       <td><span class="label label-inverse-primary">{{ ucfirst($user->role) }}</span></td>
                                       <td>{{ $user->last_login?->format('d/m/Y H:i') ?? 'Jamais connecté' }}</td>
                                       <td>
                                          <span class="label label-{{ $user->statut === 'actif' ? 'success' : 'danger' }}">
                                             {{ $user->statut === 'actif' ? 'Actif' : 'Inactif' }}
                                          </span>
                                       </td>
                                       <td class="text-nowrap">
                                          @if ($user->is(auth()->user()))
                                             <span class="text-muted">Compte actuel</span>
                                          @else
                                             <a class="btn btn-sm btn-info waves-effect" href="{{ route('admin.profile.accounts.edit', $user) }}" title="Modifier le profil">
                                                <i class="icofont icofont-edit"></i>
                                             </a>
                                             <a class="btn btn-sm btn-warning waves-effect" href="{{ route('admin.profile.accounts.password.edit', $user) }}" title="Réinitialiser le mot de passe / PIN">
                                                <i class="icofont icofont-key"></i>
                                             </a>
                                             
                                             @if ($user->role !== 'admin' && $user->statut === 'actif')
                                                <form class="d-inline" method="POST" action="{{ route('admin.profile.accounts.impersonate', $user) }}" onsubmit="return confirm('Ouvrir ce compte sans mot de passe ?');">
                                                   @csrf
                                                   <button class="btn btn-sm btn-primary waves-effect" type="submit" title="Accéder au compte sans mot de passe">
                                                      <i class="icofont icofont-login"></i>
                                                   </button>
                                                </form>
                                             @endif

                                             <form class="d-inline" method="POST" action="{{ route('admin.profile.accounts.toggle', $user) }}">
                                                @csrf 
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-{{ $user->statut === 'actif' ? 'warning' : 'success' }} waves-effect" type="submit" title="{{ $user->statut === 'actif' ? 'Désactiver' : 'Activer' }}">
                                                   <i class="icofont icofont-power"></i>
                                                </button>
                                             </form>

                                             <form class="d-inline" method="POST" action="{{ route('admin.profile.accounts.destroy', $user) }}" onsubmit="return confirm('Supprimer définitivement ce compte ?');">
                                                @csrf 
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger waves-effect" type="submit" title="Supprimer">
                                                   <i class="icofont icofont-trash"></i>
                                                </button>
                                             </form>
                                          @endif
                                       </td>
                                    </tr>
                                 @empty
                                    {{-- Tableau vide : DataTables affiche son message d'absence de ligne.
                                        Un <td colspan> dans le corps ferait compter 1 cellule au lieu de 7
                                         et déclencherait « Incorrect column count » (tn/18). --}}
                                 @endforelse
                              </tbody>
                           </table>
                        </div>
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

   <!-- DataTables -->
   <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

   <!-- Custom Scripts -->
   <script type="text/javascript" src="{{ asset('assets/js/main.min.js') }}"></script>
   <script src="{{ asset('assets/js/menu.min.js') }}"></script>

   <script>
      $(document).ready(function() {
          $('#accountsTable').DataTable({
              "language": {
                  "url": "//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json"
              }
          });
      });
   </script>
</body>
</html>