<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier le dossier élève</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>.section-header{background:#011c31;color:#fff;padding:10px 16px;font-weight:600;border-radius:4px;font-size:14px;text-transform:uppercase}.form-label{font-weight:600;font-size:13px}.error{color:#dc3545;font-size:12px}</style>
</head>
<body class="app">
<main class="app-content" style="margin-left:0">
    <div class="app-title"><div><h1><i class="fa fa-pencil"></i> Modifier le dossier</h1><p>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</p></div><a href="{{ route('admin.eleves.show', $eleve) }}" class="btn btn-secondary">Retour au dossier</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="tile p-4 mb-4"><form method="POST" action="{{ route('admin.eleves.update', $eleve) }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="section-header mb-3">Identité de l'élève</div><div class="row">
            @foreach(['nom'=>'Nom','postnom'=>'Post-nom','prenom'=>'Prénom'] as $field=>$label)<div class="col-md-4 form-group"><label class="form-label">{{ $label }} *</label><input class="form-control" name="{{ $field }}" value="{{ old($field, $eleve->$field) }}" required>@error($field)<div class="error">{{ $message }}</div>@enderror</div>@endforeach
            <div class="col-md-4 form-group"><label class="form-label">Sexe *</label><select class="form-control" name="sexe" required><option value="M" @selected(old('sexe', $eleve->sexe) === 'M')>Garçon</option><option value="F" @selected(old('sexe', $eleve->sexe) === 'F')>Fille</option></select></div>
            <div class="col-md-4 form-group"><label class="form-label">Date de naissance *</label><input class="form-control" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance', $eleve->date_naissance?->format('Y-m-d')) }}" required></div>
            <div class="col-md-4 form-group"><label class="form-label">Lieu de naissance</label><input class="form-control" name="lieu_naissance" value="{{ old('lieu_naissance', $eleve->lieu_naissance) }}"></div>
            <div class="col-md-4 form-group"><label class="form-label">Statut *</label><select class="form-control" name="statut" required>@foreach(['actif'=>'Actif','inactif'=>'Inactif','suspendu'=>'Suspendu','exclu'=>'Exclu'] as $value=>$label)<option value="{{ $value }}" @selected(old('statut', $eleve->statut) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-8 form-group"><label class="form-label">Remplacer la photo</label><input class="form-control" type="file" name="photo" accept="image/*"><small>JPG ou PNG, 2 Mo maximum</small></div>
        </div>
        <div class="section-header mb-3 mt-3">Responsables légaux</div><div class="row">
            @foreach(['nom_mere'=>'Nom de la mère / responsable *','telephone_mere'=>'Téléphone de la mère *','profession_mere'=>'Profession de la mère','email_mere'=>'E-mail de la mère','adresse_mere'=>'Adresse de la mère','nom_pere'=>'Nom du père','telephone_pere'=>'Téléphone du père','profession_pere'=>'Profession du père','email_pere'=>'E-mail du père','adresse_pere'=>'Adresse du père'] as $field=>$label)<div class="col-md-4 form-group"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ old($field, $eleve->tuteur?->$field) }}"></div>@endforeach
        </div>
        <div class="section-header mb-3 mt-3">Informations médicales</div><div class="row">
            @foreach(['groupe_sanguin'=>'Groupe sanguin','allergies_pathologies'=>'Allergies / pathologies','traitements_urgences'=>'Traitements / urgences','medecin_referent'=>'Médecin référent'] as $field=>$label)<div class="col-md-6 form-group"><label class="form-label">{{ $label }}</label><textarea class="form-control" name="{{ $field }}" rows="2">{{ old($field, $eleve->sante?->$field) }}</textarea></div>@endforeach
        </div>
        <div class="text-right"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer les modifications</button></div>
    </form></div>
    <div class="tile p-4"><div class="section-header mb-3">Ajouter un document au dossier</div><form method="POST" action="{{ route('admin.eleves.documents.store', $eleve) }}" enctype="multipart/form-data">@csrf<div class="row"><div class="col-md-4 form-group"><label class="form-label">Type de document *</label><select class="form-control" name="type_document" required><option value="">Choisir</option><option value="acte_naissance">Acte de naissance</option><option value="bulletin">Bulletin scolaire</option><option value="certificat_medical">Certificat médical</option><option value="autre">Autre document</option></select></div><div class="col-md-5 form-group"><label class="form-label">Fichier *</label><input class="form-control" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required><small>PDF, JPG ou PNG, 4 Mo maximum</small></div><div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-success w-100" type="submit"><i class="fa fa-upload"></i> Ajouter</button></div></div></form></div>
</main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>