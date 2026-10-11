<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription élève</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>.section-header{background:#011c31;color:#fff;padding:10px 16px;font-weight:600;border-radius:4px;font-size:14px;text-transform:uppercase}.photo-preview{width:140px;height:160px;border:2px dashed #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer}.photo-preview img{width:100%;height:100%;object-fit:cover}.form-label{font-weight:600;font-size:13px}.error{color:#dc3545;font-size:12px}</style>
</head>
<body class="app">
<main class="app-content" style="margin-left:0"><div class="app-title"><div><h1><i class="fa fa-user-plus"></i> Inscription d'un élève</h1><p>Constitution du dossier scolaire.</p></div><a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a></div>
<div class="tile p-4">
@if ($errors->any())<div class="alert alert-danger"><strong>Inscription impossible :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.eleves.store') }}" enctype="multipart/form-data">@csrf
    <div class="mb-4"><div class="section-header mb-3">1. Orientation et affectation scolaire</div><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Classe demandée *</label><select class="form-control" name="classe_id" required><option value="">Sélectionner une classe</option>@foreach($classes->groupBy(fn($classe) => $classe->cycle->nom) as $cycle => $classesCycle)<optgroup label="{{ $cycle }}">@foreach($classesCycle as $classe)<option value="{{ $classe->id }}" @selected(old('classe_id') == $classe->id)>{{ $classe->nom }}</option>@endforeach</optgroup>@endforeach</select>@error('classe_id')<div class="error">{{ $message }}</div>@enderror</div>
        <div class="col-md-6 mb-3"><label class="form-label">Année scolaire *</label>
            @if ($anneeEnCours)
                <input class="form-control" value="{{ $anneeEnCours->libelle }}" disabled>
                <small class="text-muted">Année en cours — modifiable dans « Années scolaires ».</small>
            @else
                <div class="alert alert-warning py-2 mb-0">Aucune année scolaire n'est en cours. <a href="{{ route('admin.annees.index') }}">En définir une</a>.</div>
            @endif
            @error('annee_scolaire_id')<div class="error">{{ $message }}</div>@enderror</div>
    </div></div>
    <div class="mb-4"><div class="section-header mb-3">2. Identité de l'élève</div><div class="row align-items-center"><div class="col-md-3 mb-3"><div class="photo-preview" onclick="document.getElementById('photo').click()"><img id="imagePreview" class="d-none" alt="Aperçu"><i id="camera" class="fa fa-camera fa-2x"></i></div><input id="photo" class="d-none" type="file" name="photo" accept="image/*"><small>Photo JPG ou PNG</small></div><div class="col-md-9"><div class="row">
        @foreach(['nom'=>'Nom','postnom'=>'Post-nom','prenom'=>'Prénom'] as $champ=>$label)<div class="col-md-4 mb-3"><label class="form-label">{{ $label }} *</label><input class="form-control" name="{{ $champ }}" value="{{ old($champ) }}" required>@error($champ)<div class="error">{{ $message }}</div>@enderror</div>@endforeach
        <div class="col-md-4 mb-3"><label class="form-label">Sexe *</label><select class="form-control" name="sexe" required><option value="">Choisir</option><option value="M">Masculin</option><option value="F">Féminin</option></select></div><div class="col-md-4 mb-3"><label class="form-label">Date de naissance *</label><input class="form-control" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance') }}" required></div><div class="col-md-4 mb-3"><label class="form-label">Lieu de naissance</label><input class="form-control" name="lieu_naissance" value="{{ old('lieu_naissance') }}"></div>
    </div></div></div></div>
    <div class="mb-4"><div class="section-header mb-3">3. Santé et renseignements médicaux</div><div class="row"><div class="col-md-3 mb-3"><label class="form-label">Groupe sanguin</label><input class="form-control" name="groupe_sanguin"></div><div class="col-md-9 mb-3"><label class="form-label">Allergies ou pathologies</label><input class="form-control" name="allergies_pathologies"></div><div class="col-md-6 mb-3"><label class="form-label">Traitements et urgences</label><textarea class="form-control" name="traitements_urgences"></textarea></div><div class="col-md-6 mb-3"><label class="form-label">Médecin référent</label><input class="form-control" name="medecin_referent"></div></div></div>
    <div class="mb-4"><div class="section-header mb-3">4. Tuteurs et responsables légaux</div><h6>Mère / responsable principal</h6><div class="row">@foreach(['nom_mere'=>'Nom complet *','profession_mere'=>'Profession','telephone_mere'=>'Téléphone *','email_mere'=>'E-mail','adresse_mere'=>'Adresse'] as $champ=>$label)<div class="col-md-4 mb-3"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $champ }}" value="{{ old($champ) }}" @if(in_array($champ,['nom_mere','telephone_mere'])) required @endif></div>@endforeach</div><h6>Père</h6><div class="row">@foreach(['nom_pere'=>'Nom complet','profession_pere'=>'Profession','telephone_pere'=>'Téléphone','email_pere'=>'E-mail','adresse_pere'=>'Adresse'] as $champ=>$label)<div class="col-md-4 mb-3"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $champ }}" value="{{ old($champ) }}"></div>@endforeach</div></div>
    <div class="mb-4"><div class="section-header mb-3">5. Documents justificatifs</div><div class="row"><div class="col-md-4"><label class="form-label">Acte de naissance</label><input class="form-control" type="file" name="acte_naissance" accept=".pdf,.jpg,.jpeg,.png"></div><div class="col-md-4"><label class="form-label">Bulletin précédent</label><input class="form-control" type="file" name="bulletin" accept=".pdf,.jpg,.jpeg,.png"></div><div class="col-md-4"><label class="form-label">Certificat médical</label><input class="form-control" type="file" name="certificat_medical" accept=".pdf,.jpg,.jpeg,.png"></div></div></div>
    <div class="text-right"><button type="reset" class="btn btn-secondary">Réinitialiser</button><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Valider et enregistrer</button></div>
</form></div></main>
<script>document.getElementById('photo').addEventListener('change', e=>{const file=e.target.files[0], image=document.getElementById('imagePreview');if(file){image.src=URL.createObjectURL(file);image.classList.remove('d-none');document.getElementById('camera').classList.add('d-none');}});</script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body></html>
