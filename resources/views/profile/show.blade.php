<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { background: #f8f9fa; }
        .profile-header { background: #0d0c0f; color: #fff; }
        .profile-avatar { width: 88px; height: 88px; object-fit: cover; border-radius: 50%; background: #795548; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 600; }
        .profile-content { max-width: 960px; margin: 0 auto; }
    </style>
</head>
<body>
    @php
        $roleLabels = ['admin' => 'Administrateur', 'prefetture' => 'Préfecture', 'titulaire' => 'Titulaire', 'professeur' => 'Professeur', 'comptable' => 'Comptable'];
    @endphp
    <header class="profile-header p-3 d-flex align-items-center justify-content-between">
        <a class="text-white" href="{{ route(auth()->user()->dashboardRouteName()) }}"><i class="fa fa-arrow-left"></i> Tableau de bord</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Déconnexion</button></form>
    </header>
    @include('partials.impersonation-banner')
    <main class="container py-4 profile-content">
        <div class="app-title">
            <div><h1><i class="fa fa-user"></i> Mon profil</h1><p>Vos informations personnelles et vos coordonnées.</p></div>
            @if (auth()->user()?->role === 'admin')
                <a class="btn btn-primary" href="{{ route('profile.edit') }}"><i class="fa fa-pencil"></i> Modifier mon profil</a>
            @else
                <span class="text-muted"><i class="fa fa-lock"></i> Vos informations et coordonnées sont gérées par l'administration.</span>
            @endif
        </div>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <section class="tile">
            <div class="d-flex align-items-center mb-4">
                @if ($user->photo)
                    <img class="profile-avatar mr-3" src="{{ asset('storage/'.$user->photo) }}" alt="Photo de profil">
                @else
                    <span class="profile-avatar mr-3">{{ strtoupper(substr($user->prenom ?: $user->username, 0, 1)) }}</span>
                @endif
                <div><h2 class="mb-1">{{ trim($user->prenom.' '.$user->postnom.' '.$user->nom) ?: $user->username }}</h2><span class="text-muted">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0"><tbody>
                    <tr><th>Nom d'utilisateur</th><td>{{ $user->username }}</td></tr>
                    <tr><th>Adresse e-mail</th><td>{{ $user->email }}</td></tr>
                    <tr><th>Téléphone</th><td>{{ $user->telephone ?: 'Non renseigné' }}</td></tr>
                    <tr><th>Adresse</th><td>{{ $user->adresse ?: 'Non renseignée' }}</td></tr>
                    <tr><th>Date de naissance</th><td>{{ $user->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</td></tr>
                </tbody></table>
            </div>
        </section>
    </main>
</body>
</html>
