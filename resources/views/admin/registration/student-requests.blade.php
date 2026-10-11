<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demandes d’inscription</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { min-height: 100vh; background: #f2f5f2; color: #263a36; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 24px; color: #fff; background: #193c39; }
        .page-header a { color: #fff; }
        .page-brand { font-family: Arial, sans-serif; }
        .content { width: min(1080px, calc(100% - 32px)); margin: 28px auto 48px; }
        .heading { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
        .eyebrow { margin-bottom: 6px; color: #71817d; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; color: #193c39; font-size: 26px; font-weight: 800; }
        .heading p { margin: 6px 0 0; color: #71817d; font-size: 12px; }
        .request-card { margin-bottom: 13px; padding: 20px; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; box-shadow: 0 8px 22px rgba(25,60,57,.045); }
        .request-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .request-name { margin: 0; color: #193c39; font-size: 16px; font-weight: 800; }
        .request-meta { margin-top: 5px; color: #71817d; font-size: 11px; }
        .request-details { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 17px 0; padding: 13px 0; border-top: 1px solid #edf1ed; border-bottom: 1px solid #edf1ed; }
        .detail-label { display: block; margin-bottom: 3px; color: #85918b; font-size: 9px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
        .detail-value { color: #344c47; font-size: 12px; font-weight: 600; }
        .review-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end; }
        .form-group { margin: 0; }
        .form-label { color: #304743; font-size: 11px; font-weight: 700; }
        .form-control { min-height: 39px; border-color: #dbe4dc; font-size: 12px; }
        .decision-actions { display: flex; gap: 8px; }
        .reject-form { display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 12px; }
        .reject-form input { max-width: 320px; }
        .empty-state { padding: 48px 20px; color: #71817d; background: #fff; border: 1px solid #e1e8e2; border-radius: 9px; text-align: center; }
        .empty-state i { display: block; margin-bottom: 10px; color: #8ca29a; font-size: 24px; }
        @media (max-width: 700px) { .page-header { padding: 14px 16px; } .content { width: calc(100% - 24px); margin-top: 20px; } .heading { align-items: flex-start; flex-direction: column; gap: 12px; } .request-details { grid-template-columns: repeat(2, minmax(0,1fr)); } .review-form { grid-template-columns: minmax(0,1fr); } .decision-actions { justify-content: flex-end; } .reject-form { align-items: stretch; flex-direction: column; } .reject-form input { max-width: none; } }
    </style>
</head>
<body>
    <header class="page-header"><a href="{{ route('admin.dashboard') }}"><i class="fa fa-arrow-left"></i> Tableau de bord</a><span><span class="page-brand">{{ $systemSettings->nom_court }}</span> · Administration</span></header>
    <main class="content">
        <div class="heading"><div><p class="eyebrow">Admissions</p><h1>Demandes d’inscription</h1><p>Une acceptation crée le dossier élève et son inscription dans la classe choisie.</p></div><span class="badge badge-warning">{{ $requests->count() }} en attente</span></div>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @forelse ($requests as $application)
            <article class="request-card">
                <div class="request-top"><div><h2 class="request-name">{{ $application->nom }} {{ $application->postnom }} {{ $application->prenom }}</h2><div class="request-meta">Demande #{{ $application->id }} · {{ $application->created_at?->format('d/m/Y H:i') }}</div></div><span class="badge badge-warning">En attente</span></div>
                <div class="request-details">
                    <div><span class="detail-label">Naissance</span><span class="detail-value">{{ $application->date_naissance?->format('d/m/Y') }} · {{ $application->sexe === 'M' ? 'Garçon' : 'Fille' }}</span></div>
                    <div><span class="detail-label">Responsable</span><span class="detail-value">{{ $application->nom_mere }}</span></div>
                    <div><span class="detail-label">Téléphone</span><span class="detail-value">{{ $application->telephone_mere }}</span></div>
                    <div><span class="detail-label">E-mail</span><span class="detail-value">{{ $application->email_mere ?: 'Non renseigné' }}</span></div>
                    <div><span class="detail-label">Classe souhaitée</span><span class="detail-value">{{ $application->classe?->nom ?: 'À choisir' }}</span></div>
                    <div><span class="detail-label">Année souhaitée</span><span class="detail-value">{{ $application->anneeScolaire?->libelle ?: 'À choisir' }}</span></div>
                </div>
                <form class="review-form" method="POST" action="{{ route('admin.registration.students.approve', $application) }}">
                    @csrf
                    <div class="form-group"><label class="form-label" for="class-{{ $application->id }}">Classe à attribuer</label><select class="form-control" id="class-{{ $application->id }}" name="classe_id" required><option value="">Choisir une classe</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected(old('classe_id', $application->classe_id) == $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label" for="year-{{ $application->id }}">Année scolaire</label><select class="form-control" id="year-{{ $application->id }}" name="annee_scolaire_id" required><option value="">Choisir une année</option>@foreach ($schoolYears as $year)<option value="{{ $year->id }}" @selected(old('annee_scolaire_id', $application->annee_scolaire_id) == $year->id)>{{ $year->libelle }}</option>@endforeach</select></div>
                    <div class="decision-actions"><button class="btn btn-success" type="submit" @disabled($classes->isEmpty() || $schoolYears->isEmpty())><i class="fa fa-check"></i> Accepter et inscrire</button></div>
                </form>
                <form class="reject-form" method="POST" action="{{ route('admin.registration.students.reject', $application) }}">
                    @csrf
                    <input class="form-control" name="motif_refus" maxlength="1000" placeholder="Motif de refus (facultatif)" aria-label="Motif de refus">
                    <button class="btn btn-outline-danger" type="submit" onclick="return confirm('Refuser cette demande d’inscription ?')"><i class="fa fa-times"></i> Refuser</button>
                </form>
            </article>
        @empty
            <div class="empty-state"><i class="fa fa-inbox"></i>Aucune demande d’inscription en attente.</div>
        @endforelse
    </main>
</body>
</html>
