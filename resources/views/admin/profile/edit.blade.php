<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Modifier mon profil - Able Pro Admin</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,500,700" rel="stylesheet">

    <!-- Icons Able Pro -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/themify-icons/themify-icons.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/icofont/css/icofont.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/simple-line-icons/css/simple-line-icons.css') }}">
    
    <!-- Required Framework -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">

    <!-- Style.css Able -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">

    <style>
        :root {
            --able-primary: #4680ff;
            --able-secondary: #576574;
            --able-bg: #f4f7fa;
            --able-header-bg: #ffffff;
            --able-sidebar-bg: #ffffff;
        }

        body {
            background-color: var(--able-bg);
            font-family: 'Ubuntu', sans-serif;
            color: #374151;
            margin: 0;
        }

        /* --- ABLE HEADER --- */
        .pc-header {
            height: 70px;
            background: var(--able-header-bg);
            border-bottom: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
        }

        .pc-header .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 18px;
            color: var(--able-primary);
            text-decoration: none;
        }

        .pc-header .header-search {
            position: relative;
            width: 280px;
        }

        .pc-header .header-search input {
            border-radius: 20px;
            padding-left: 35px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 13px;
        }

        .pc-header .header-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .header-nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-nav-right .icon-link {
            color: #64748b;
            font-size: 18px;
            position: relative;
            text-decoration: none;
        }

        .header-nav-right .badge-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }

        /* --- ABLE MAIN WRAPPER --- */
        .pc-container {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }

        /* --- ABLE SIDEBAR --- */
        .pc-sidebar {
            width: 260px;
            background: var(--able-sidebar-bg);
            border-right: 1px solid #e2e8f0;
            flex-shrink: 0;
            padding: 20px 0;
            position: fixed;
            top: 70px;
            bottom: 0;
            overflow-y: auto;
            z-index: 1020;
        }

        .pc-sidebar .nav-caption {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 12px 25px 6px;
            letter-spacing: 0.5px;
        }

        .pc-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .pc-sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            color: #64748b;
        }

        .pc-sidebar .nav-link:hover, .pc-sidebar .nav-link.active {
            background: #f1f5f9;
            color: var(--able-primary);
            border-left-color: var(--able-primary);
        }

        .pc-sidebar .nav-link:hover i, .pc-sidebar .nav-link.active i {
            color: var(--able-primary);
        }

        /* --- CONTENT AREA --- */
        .pc-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 30px;
            width: calc(100% - 260px);
        }

        /* --- CARDS & AVATARS --- */
        .able-card {
            background: #ffffff;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .avatar-lg {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            background: var(--able-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(70, 128, 255, 0.25);
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%;
            background: var(--able-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #334155;
            margin-bottom: 6px;
        }

        .section-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 991px) {
            .pc-sidebar { display: none; }
            .pc-content { margin-left: 0; width: 100%; padding: 15px; }
        }
    </style>
</head>
<body>

    <!-- ABLE PRO HEADER -->
    <header class="pc-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="header-brand">
                <i class="ti-layers-alt"></i>
                <span>ABLE PRO</span>
            </a>
            <div class="header-search d-none d-md-block">
                <i class="ti-search"></i>
                <input type="text" class="form-control" placeholder="Rechercher...">
            </div>
        </div>

        <div class="header-nav-right">
            <a href="#" class="icon-link" title="Notifications">
                <i class="ti-bell"></i>
                <span class="badge-dot"></span>
            </a>

            <a href="#" class="icon-link" title="Messages">
                <i class="ti-email"></i>
            </a>

            <div class="dropdown d-flex align-items-center gap-2">
                @if ($user->photo)
                    <img class="avatar-sm" src="{{ asset('storage/'.$user->photo) }}" alt="Photo">
                @else
                    <span class="avatar-sm">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                @endif
                <div class="d-none d-lg-block text-left">
                    <div class="font-weight-bold" style="font-size: 13px; line-height: 1.2;">{{ $user->prenom }} {{ $user->nom }}</div>
                    <small class="text-muted d-block" style="font-size: 11px;">Administrateur</small>
                </div>
                
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button class="btn btn-sm btn-light text-danger border-0" type="submit" title="Déconnexion">
                        <i class="ti-power-off"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- ABLE MAIN LAYOUT CONTAINER -->
    <div class="pc-container">

        <!-- ABLE SIDEBAR NAVIGATION -->
        <aside class="pc-sidebar">
            <div class="nav-caption">Tableau de bord</div>
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="ti-home"></i> <span>Dashboard</span>
            </a>

            <div class="nav-caption">Profil & Configuration</div>
            <a class="nav-link" href="{{ route('admin.profile.show') }}">
                <i class="ti-user"></i> <span>Mon Profil</span>
            </a>
            <a class="nav-link active" href="{{ route('admin.profile.edit') }}">
                <i class="ti-pencil-alt"></i> <span>Modifier Profil</span>
            </a>
            {{-- <a class="nav-link" href="{{ route('admin.profile.designer') ?? '#' }}"> --}}
                <i class="ti-palette"></i> <span>Able Designer</span>
            </a>
            <a class="nav-link" href="{{ route('admin.profile.accounts') }}">
                <i class="ti-id-badge"></i> <span>Tous les comptes</span>
            </a>

            <div class="nav-caption">Gestion Scolaire</div>
            <a class="nav-link" href="{{ route('admin.classes.index') }}">
                <i class="icofont icofont-student-alt"></i> <span>Classes & Élèves</span>
            </a>
            <a class="nav-link" href="{{ route('admin.salles.index') }}">
                <i class="icofont icofont-building-alt"></i> <span>Salles de classe</span>
            </a>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="pc-content">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="font-weight-bold mb-1" style="color:#0f172a;">Modifier le Profil</h3>
                    <p class="text-muted mb-0" style="font-size: 13px;">Gérez vos informations personnelles et le design de votre interface Able Admin.</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm px-3" href="{{ route('admin.profile.show') }}">
                    <i class="ti-arrow-left mr-1"></i> Retour au profil
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <strong class="d-block mb-1"><i class="ti-alert"></i> Erreur de validation :</strong>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card able-card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                        @csrf 
                        @method('PUT')

                        <!-- Section 1 : Photo & Identity -->
                        <div class="section-header">
                            <span class="section-title"><i class="ti-id-badge text-primary"></i> Informations Personnelles</span>
                        </div>

                        <div class="row align-items-center mb-4">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                @if ($user->photo)
                                    <img id="preview" class="avatar-lg" src="{{ asset('storage/'.$user->photo) }}" alt="Photo de profil">
                                @else
                                    <span id="placeholder" class="avatar-lg">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                                    <img id="preview" class="avatar-lg d-none" alt="Aperçu de la photo">
                                @endif
                            </div>
                            <div class="col-md-9">
                                <label class="form-label" for="photo">Photo de profil</label>
                                <input class="form-control" id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png">
                                <small class="text-muted d-block mt-1">Sert d'avatar principal dans la barre supérieure et le menu. Max 2 Mo.</small>
                            </div>
                        </div>

                        <div class="row">
                            @foreach(['nom'=>'Nom','postnom'=>'Post-nom','prenom'=>'Prénom'] as $field => $label)
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ $label }}</label>
                                    <input class="form-control @error($field) is-invalid @enderror" name="{{ $field }}" value="{{ old($field, $user->$field) }}" maxlength="50">
                                </div>
                            @endforeach

                            <div class="col-md-4 form-group mb-3">
                                <label class="form-label">Date de naissance</label>
                                <input class="form-control" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance', $user->date_naissance?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-label">Téléphone</label>
                                <input class="form-control" name="telephone" value="{{ old('telephone', $user->telephone) }}" maxlength="20">
                            </div>
                            <div class="col-md-8 form-group mb-3">
                                <label class="form-label">Adresse physique</label>
                                <textarea class="form-control" name="adresse" rows="2">{{ old('adresse', $user->adresse) }}</textarea>
                            </div>
                        </div>

                        <!-- Section 2 : Credentials -->
                        <div class="section-header mt-4">
                            <span class="section-title"><i class="ti-lock text-primary"></i> Identifiants de Connexion</span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                                <input class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $user->username) }}" required maxlength="50">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Adresse E-mail <span class="text-danger">*</span></label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="100">
                            </div>
                        </div>

                        <!-- Section 3 : Able Pro Designer Settings -->
                        <div class="section-header mt-4">
                            <span class="section-title"><i class="ti-settings text-primary"></i> Able Pro Designer & Personnalisation</span>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-label">Couleur d'accentuation</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_color" value="{{ old('theme_color', $user->theme_color ?? '#4680ff') }}">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-label">Mode d'affichage</label>
                                <select class="form-control" name="theme_mode">
                                    <option value="light" {{ old('theme_mode', $user->theme_mode ?? 'light') == 'light' ? 'selected' : '' }}>Mode Light</option>
                                    <option value="dark" {{ old('theme_mode', $user->theme_mode ?? 'light') == 'dark' ? 'selected' : '' }}>Mode Dark</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-label">Disposition Menu</label>
                                <select class="form-control" name="sidebar_option">
                                    <option value="default">Fixe (Standard)</option>
                                    <option value="collapsed">Réduit (Icones uniquement)</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-3">
                            <button class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" type="submit">
                                <i class="ti-save mr-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Script JavaScript pour l'aperçu dynamique de la photo -->
    <script>
        document.getElementById('photo').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const preview = document.getElementById('preview');
            const placeholder = document.getElementById('placeholder');

            if (preview) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        });
    </script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>