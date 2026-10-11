<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amorçage du système — {{ $systemSettings->nom_court ?? 'Établissement' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; background: #0b0b0c; color: #e4e6eb; min-height: 100vh; display: flex; align-items: center; }
        .panneau { max-width: 620px; margin: 40px auto; background: #17181a; border: 1px solid #2f3031; border-radius: 16px; padding: 28px; }
        .titree { font-weight: 800; letter-spacing: -.02em; }
        .form-label { font-size: .78rem; font-weight: 700; color: #b0b3b8; }
        .form-control { background: #242526; border: 1px solid #3a3b3c; color: #e4e6eb; }
        .form-control:focus { background: #242526; color: #fff; border-color: #0866ff; box-shadow: 0 0 0 .2rem rgba(8,102,255,.15); }
        .form-control::placeholder { color: #6b7280; }
        .astuce { background: #1f2937; border: 1px solid #374151; border-radius: 10px; padding: 10px 12px; font-size: .8rem; color: #cbd5e1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="panneau">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-lock fs-3 text-primary"></i>
                <h1 class="h4 titree mb-0">Amorçage du système</h1>
            </div>
            <p class="text-muted small mb-3">
                Créez le <strong>premier compte administrateur</strong>. Cette page disparaît
                automatiquement dès qu'un administrateur existe.
            </p>

            <div class="astuce mb-3">
                <i class="bi bi-info-circle me-1"></i> Gardez cette adresse confidentielle. Elle permet d'activer
                le système sur une installation neuve.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">@foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('systeme.installation.store') }}">
                @csrf
                @if (config('install.token'))
                    <input type="hidden" name="cle" value="{{ request('cle') }}">
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="nom">Nom *</label>
                        <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}" maxlength="50" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="postnom">Post-nom</label>
                        <input class="form-control" id="postnom" name="postnom" value="{{ old('postnom') }}" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="prenom">Prénom *</label>
                        <input class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}" maxlength="50" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">E-mail *</label>
                        <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Identifiant de connexion *</label>
                        <input class="form-control" id="username" name="username" value="{{ old('username') }}" maxlength="50" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Mot de passe * (8 caractères min.)</label>
                        <input class="form-control" id="password" type="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirmation *</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-4 fw-bolder" type="submit">
                    <i class="bi bi-person-plus me-1"></i> Créer l'administrateur et activer le système
                </button>
            </form>

            <p class="text-muted small mt-3 mb-0">
                Déjà un compte ? <a class="fw-bolder" href="{{ route('login') }}">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
