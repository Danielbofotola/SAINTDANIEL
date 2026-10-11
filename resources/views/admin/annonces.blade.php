<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Annonces aux utilisateurs | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>
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
                <h1><i class="fa fa-bullhorn"></i> Lancer une notification</h1>
                <p>Diffusez une annonce à tous les utilisateurs ou à un rôle précis — elle apparaît dans leur cloche et sur « Mes notifications ».</p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                <a class="btn btn-info" href="{{ route('admin.profile.online') }}"><i class="fa fa-plug"></i> Utilisateurs en ligne</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-md-4"><div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Comptes</h4><p><b>{{ $totalComptes }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small info coloured-icon"><i class="icon fa fa-bullhorn fa-3x"></i><div class="info"><h4>Annonces lancées</h4><p><b>{{ $annonces->total() }}</b></p></div></div></div>
            <div class="col-md-4"><div class="widget-small warning coloured-icon"><i class="icon fa fa-bell fa-3x"></i><div class="info"><h4>Notifications déposées</h4><p><b>{{ $notificationsEnvoyees }}</b></p></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="tile">
                    <h4 class="tile-title">Nouvelle annonce</h4>
                    <form method="POST" action="{{ route('admin.annonces.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Titre *</label>
                            <input class="form-control" name="titre" maxlength="150" value="{{ old('titre') }}" required placeholder="Ex. Réunion pédagogique vendredi">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message *</label>
                            <textarea class="form-control" name="message" rows="5" maxlength="1000" required placeholder="Détail de l’annonce…">{{ old('message') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label">Niveau *</label>
                                <select class="form-control" name="niveau" required>
                                    @foreach ($niveaux as $valeur => $libelle)
                                        <option value="{{ $valeur }}" @selected(old('niveau', 'info') === $valeur)>{{ $libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Destinataires *</label>
                                <select class="form-control" name="cible" required>
                                    @foreach ($cibles as $valeur => $libelle)
                                        <option value="{{ $valeur }}" @selected(old('cible', 'tous') === $valeur)>
                                            {{ $libelle }}@if ($valeur !== 'tous') ({{ $comptesParRole[$valeur] ?? 0 }})@else ({{ $totalComptes }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lien à ouvrir (facultatif)</label>
                            <input class="form-control" name="lien" maxlength="255" value="{{ old('lien') }}" placeholder="https://… ou /admin/annonces">
                        </div>
                        <button class="btn btn-primary btn-block" type="submit" @disabled($totalComptes === 0)><i class="fa fa-bullhorn"></i> Envoyer la notification</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="tile">
                    <h4 class="tile-title">Historique des annonces</h4>
                    @forelse ($annonces as $annonce)
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge badge-{{ ['info' => 'info', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'][$annonce->niveau] ?? 'info' }}">{{ $annonce->libelleNiveau() }}</span>
                                    <strong class="ml-1">{{ $annonce->titre }}</strong>
                                    <div class="text-muted" style="font-size:13px">
                                        {{ $annonce->libelleCible() }} · {{ $annonce->destinataires }} destinataire(s)
                                        · {{ $annonce->auteur?->username ?? 'administration' }}
                                        · {{ $annonce->created_at?->format('d/m/Y H:i') }}
                                        @if ($annonce->lien) · <i class="fa fa-link"></i> {{ $annonce->lien }}@endif
                                    </div>
                                    <div class="mt-1">{{ $annonce->message }}</div>
                                </div>
                                <form method="POST" action="{{ route('admin.annonces.destroy', $annonce) }}" onsubmit="return confirm('Supprimer cette annonce et retirer ses notifications ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune annonce lancée pour l’instant.</p>
                    @endforelse
                    <div class="mt-3">{{ $annonces->links() }}</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
