<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil — Caisse | {{ $systemSettings->nom_court }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#d0dbe5; font-family:'Segoe UI', system-ui, sans-serif; color:#1e293b; font-size:.85rem; }
        .carte { background:#f2f5f8; border:1px solid #b0c2d0; border-radius:8px; padding:14px; box-shadow:0 2px 6px rgba(0,0,0,.06); }
        .titre { font-size:.95rem; font-weight:700; color:#0f172a; text-transform:uppercase; margin-bottom:8px; }
        .kpi { font-size:1.5rem; font-weight:800; color:#107c41; }
    </style>
</head>
<body class="p-3">
@php($money = fn ($v, $d = 'USD') => \App\Support\Monnaie::format($v, $d))
@php($nomComplet = trim($user->nom.' '.$user->postnom.' '.$user->prenom) ?: $user->username)
<div class="container-fluid">
    <div class="carte mb-3 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            @if ($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}" alt="" class="rounded-circle me-3" style="width:56px;height:56px;object-fit:cover;">
            @else
                <span class="rounded-circle me-3 d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width:56px;height:56px;background:#107c41;font-size:1.4rem;">{{ mb_strtoupper(mb_substr($user->prenom ?: $user->username, 0, 1)) }}</span>
            @endif
            <div>
                <div class="titre mb-0">{{ $nomComplet }}</div>
                <div class="text-muted">{{ '@'.$user->username }} · Comptable (caisse)@if($user->email) · {{ $user->email }}@endif</div>
            </div>
        </div>
        <div><a href="{{ route('comptable.dashboard') }}" class="btn btn-sm btn-secondary"><i class="bi bi-speedometer2 me-1"></i> Tableau de bord de caisse</a></div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="carte text-center"><div class="text-muted">Paiements @if($annee)({{ $annee->libelle }})@endif</div><div class="kpi">{{ $nbPaiements }}</div></div></div>
        <div class="col-md-3"><div class="carte text-center"><div class="text-muted">Encaissements du jour</div><div class="kpi">{{ $encaissementsJour }}</div></div></div>
        <div class="col-md-6"><div class="carte text-center"><div class="text-muted">Total encaissé</div>
            <div class="kpi">
                @forelse ($encaisseParDevise as $devise => $montant){{ $money($montant, $devise) }}@if (! $loop->last) · @endif @empty {{ $money(0) }} @endforelse
            </div>
        </div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="carte mb-3">
                <div class="titre"><i class="bi bi-person-lines-fill me-1"></i> Mes informations</div>
                <form method="POST" action="{{ route('comptable.profil.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Nom</label><input class="form-control form-control-sm" name="nom" value="{{ old('nom', $user->nom) }}" maxlength="50"></div>
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Post-nom</label><input class="form-control form-control-sm" name="postnom" value="{{ old('postnom', $user->postnom) }}" maxlength="50"></div>
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Prénom</label><input class="form-control form-control-sm" name="prenom" value="{{ old('prenom', $user->prenom) }}" maxlength="50"></div>
                        <div class="col-md-6"><label class="form-label mb-1 fw-bold">E-mail *</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="100" required></div>
                        <div class="col-md-6"><label class="form-label mb-1 fw-bold">Téléphone</label><input class="form-control form-control-sm" name="telephone" value="{{ old('telephone', $user->telephone) }}" maxlength="20"></div>
                        <div class="col-md-8"><label class="form-label mb-1 fw-bold">Adresse</label><input class="form-control form-control-sm" name="adresse" value="{{ old('adresse', $user->adresse) }}" maxlength="500"></div>
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Photo</label><input class="form-control form-control-sm" type="file" name="photo" accept="image/*"></div>
                    </div>
                    <button class="btn btn-success btn-sm mt-3" type="submit"><i class="bi bi-save me-1"></i> Enregistrer</button>
                </form>
            </div>

            <div class="carte">
                <div class="titre"><i class="bi bi-shield-lock me-1"></i> Mot de passe</div>
                <form method="POST" action="{{ route('comptable.profil.password') }}">
                    @csrf @method('PATCH')
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Actuel *</label><input class="form-control form-control-sm" type="password" name="current_password" required></div>
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Nouveau *</label><input class="form-control form-control-sm" type="password" name="password" required></div>
                        <div class="col-md-4"><label class="form-label mb-1 fw-bold">Confirmation *</label><input class="form-control form-control-sm" type="password" name="password_confirmation" required></div>
                    </div>
                    <button class="btn btn-primary btn-sm mt-3" type="submit"><i class="bi bi-key me-1"></i> Modifier le mot de passe</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="carte">
                <div class="titre"><i class="bi bi-receipt me-1"></i> Mes derniers encaissements</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Reçu</th><th>Élève</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            @forelse ($derniersPaiements as $paiement)
                                <tr>
                                    <td><span class="fw-bold">{{ $paiement->num_recu }}</span><br><small class="text-muted">{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</small></td>
                                    <td>{{ $paiement->inscription?->eleve?->nom }} {{ $paiement->inscription?->eleve?->prenom }}<br><small class="text-muted">{{ $paiement->inscription?->classe?->nom }}</small></td>
                                    <td class="text-end fw-bold">{{ $money($paiement->montant_paye, $paiement->fraisScolaire?->devise) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Aucun encaissement enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
