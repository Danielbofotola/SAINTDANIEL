<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commuter les modules</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-toggle-on"></i> Commuter les modules</h1>
                <p>Activez ou désactivez les modules optionnels de l'établissement.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.profile.settings') }}" class="btn btn-primary"><i class="fa fa-cog"></i> Paramètres établissement</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-sliders"></i> Modules optionnels</h4>
            <form method="POST" action="{{ route('admin.modules.update') }}">
                @csrf
                @method('PUT')

                <div class="border-bottom py-3 d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Saisie des notes</strong>
                        <p class="text-muted mb-0 small">Autorise l'ouverture de la page de saisie des notes d'une évaluation. Lorsqu'il est désactivé, la saisie est bloquée sur toute l'application.</p>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="hidden" name="saisie_notes_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="saisie_notes_active" name="saisie_notes_active" value="1" @checked(old('saisie_notes_active', $settings->saisie_notes_active))>
                        <label class="custom-control-label" for="saisie_notes_active">{{ $settings->saisie_notes_active ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="py-3 d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Portail Parents</strong>
                        <p class="text-muted mb-0 small">Ouvre l'espace de communication des parents (<code>/espace-parent</code>) : profil, annonces et messagerie avec le personnel.</p>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="hidden" name="portail_parents_actif" value="0">
                        <input type="checkbox" class="custom-control-input" id="portail_parents_actif" name="portail_parents_actif" value="1" @checked(old('portail_parents_actif', $settings->portail_parents_actif))>
                        <label class="custom-control-label" for="portail_parents_actif">{{ $settings->portail_parents_actif ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="py-3 d-flex justify-content-between align-items-start border-top">
                    <div>
                        <strong>Délibération &amp; passage</strong>
                        <p class="text-muted mb-0 small">Active l'écran de délibération : critères, décisions par élève et passage de classe en masse (réutilise la moyenne du bulletin).</p>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="hidden" name="deliberation_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="deliberation_active" name="deliberation_active" value="1" @checked(old('deliberation_active', $settings->deliberation_active))>
                        <label class="custom-control-label" for="deliberation_active">{{ $settings->deliberation_active ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="py-3 d-flex justify-content-between align-items-start border-top">
                    <div>
                        <strong>Reçus de paiement</strong>
                        <p class="text-muted mb-0 small">Affiche la page imprimable du reçu après un encaissement. Désactivé, la page reçu est fermée (404).</p>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="hidden" name="module_recus_actif" value="0">
                        <input type="checkbox" class="custom-control-input" id="module_recus_actif" name="module_recus_actif" value="1" @checked(old('module_recus_actif', $settings->module_recus_actif ?? true))>
                        <label class="custom-control-label" for="module_recus_actif">{{ ($settings->module_recus_actif ?? true) ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="py-3 d-flex justify-content-between align-items-start border-top">
                    <div>
                        <strong>Recouvrement</strong>
                        <p class="text-muted mb-0 small">Ouvre le suivi des encaissements par classe, période et mois. Désactivé, la page recouvrement est fermée (404).</p>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="hidden" name="module_recouvrement_actif" value="0">
                        <input type="checkbox" class="custom-control-input" id="module_recouvrement_actif" name="module_recouvrement_actif" value="1" @checked(old('module_recouvrement_actif', $settings->module_recouvrement_actif ?? true))>
                        <label class="custom-control-label" for="module_recouvrement_actif">{{ ($settings->module_recouvrement_actif ?? true) ? 'Actif' : 'Inactif' }}</label>
                    </div>
                </div>

                <div class="mt-3 text-right"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer</button></div>
            </form>
        </div>
    </main>
</body>
</html>
