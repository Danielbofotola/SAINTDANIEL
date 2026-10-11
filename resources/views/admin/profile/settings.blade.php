<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paramètres de l’application et du bulletin</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        body { min-height: 100vh; background: #f2f5f2; color: #263a36; }
        .settings-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; color: #fff; background: #193c39; }
        .settings-header a { color: #fff; }
        .settings-wrap { width: min(880px, calc(100% - 32px)); margin: 34px auto 60px; }
        .settings-heading { margin-bottom: 20px; }
        .eyebrow { margin: 0 0 7px; color: #71817d; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; color: #193c39; font-family: Arial, sans-serif; font-size: 26px; font-weight: 700; }
        .settings-heading p { margin: 7px 0 0; color: #71817d; font-size: 13px; }
        .settings-panel { padding: 25px; margin-bottom: 18px; background: #fff; border: 1px solid #e1e8e2; border-radius: 10px; box-shadow: 0 10px 28px rgba(25,60,57,.06); }
        .panel-title { display: flex; align-items: center; gap: 9px; margin: 0 0 4px; color: #193c39; font-family: Arial, sans-serif; font-size: 17px; font-weight: 700; }
        .panel-title i { color: #6e9b82; }
        .panel-hint { margin: 0 0 18px; color: #71817d; font-size: 12px; }
        .section-title { margin: 22px 0 14px; padding-bottom: 8px; color: #193c39; border-bottom: 1px solid #e5ebe5; font-size: 12px; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; }
        .form-label { color: #304743; font-size: 12px; font-weight: 700; }
        .form-control { min-height: 42px; border-color: #dbe4dc; }
        .form-control:focus { border-color: #6e9b82; box-shadow: 0 0 0 3px rgba(110,155,130,.14); }
        .settings-hint { margin-top: 6px; color: #71817d; font-size: 11px; }
        .switch-row { display: flex; align-items: flex-start; gap: 12px; padding: 13px 15px; margin-bottom: 10px; background: #f7faf7; border: 1px solid #e6ece7; border-radius: 9px; }
        .switch-row input[type="checkbox"] { width: 18px; height: 18px; margin-top: 2px; }
        .switch-row .switch-titre { color: #223a35; font-size: 13px; font-weight: 700; }
        .switch-row .switch-texte { margin: 3px 0 0; color: #71817d; font-size: 11px; }
        .settings-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
        @media (max-width: 520px) { .settings-header { padding: 14px 16px; } .settings-wrap { margin-top: 23px; } .settings-panel { padding: 19px; } h1 { font-size: 23px; } }
    </style>
</head>
<body>
    <header class="settings-header">
        <a href="{{ route('admin.profile.show') }}"><i class="fa fa-arrow-left"></i> Retour au profil admin</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light btn-sm" type="submit">Déconnexion</button></form>
    </header>
    <main class="settings-wrap">
        <div class="settings-heading">
            <p class="eyebrow">Configuration générale</p>
            <h1>Paramètres de l’application et du bulletin</h1>
            <p>Tout se règle ici, une seule fois : l’identité de l’école, les modules actifs et la présentation du bulletin.</p>
        </div>

        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ route('admin.profile.settings.update') }}">
            @csrf
            @method('PUT')

            <section class="settings-panel">
                <h2 class="panel-title"><i class="fa fa-university"></i> Identité de l’établissement</h2>
                <p class="panel-hint">Ces informations remplacent le nom et les coordonnées affichés partout dans l’application (vitrine publique, en-têtes, bulletins).</p>

                <div class="form-group">
                    <label class="form-label" for="nom_etablissement">Nom complet de l’établissement *</label>
                    <input class="form-control" id="nom_etablissement" name="nom_etablissement" value="{{ old('nom_etablissement', $settings->nom_etablissement) }}" maxlength="150" required>
                    <div class="settings-hint">Exemple : COMPLEXE SCOLAIRE BABAZIN — c’est ce nom qui s’affiche en haut de la vitrine publique et du bulletin.</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nom_court">Nom court / sigle *</label>
                    <input class="form-control" id="nom_court" name="nom_court" value="{{ old('nom_court', $settings->nom_court) }}" maxlength="60" required>
                    <div class="settings-hint">Utilisé dans les logos, les en-têtes et les menus.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control" id="telephone" name="telephone" value="{{ old('telephone', $settings->telephone) }}" maxlength="30">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="email">E-mail</label>
                        <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $settings->email) }}" maxlength="150">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="adresse">Adresse</label>
                    <textarea class="form-control" id="adresse" name="adresse" rows="2" maxlength="1000">{{ old('adresse', $settings->adresse) }}</textarea>
                </div>
            </section>

            <section class="settings-panel">
                <h2 class="panel-title"><i class="fa fa-money"></i> Devises &amp; taux de change</h2>
                <p class="panel-hint">Devise par défaut des nouveaux tarifs, et taux de change du franc congolais (CDF) par rapport au dollar (USD) — pré-rempli lors des encaissements.</p>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="devise">Devise par défaut</label>
                        <select class="form-control" id="devise" name="devise">
                            @foreach (\App\Support\Monnaie::DEVISES as $code => $libelle)
                                <option value="{{ $code }}" @selected(old('devise', $settings->devise ?? 'USD') === $code)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                        <div class="settings-hint">Devise proposée par défaut lors de la création d'un tarif.</div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="taux_change">Taux de change (FC pour 1 $)</label>
                        <input class="form-control" id="taux_change" type="number" step="0.01" min="0" name="taux_change" value="{{ old('taux_change', $settings->taux_change ?? \App\Support\Monnaie::TAUX_DEFAUT) }}">
                        <div class="settings-hint">Nombre de francs congolais pour 1 dollar. Sert à convertir un paiement en FC vers la devise du tarif.</div>
                    </div>
                </div>
            </section>

            <section class="settings-panel">
                <h2 class="panel-title"><i class="fa fa-toggle-on"></i> Options de l’application</h2>
                <p class="panel-hint">Activez ou désactivez les modules selon l’organisation de l’école. Une case décochée ferme le module à tous les rôles concernés.</p>

                <div class="switch-row">
                    <input type="hidden" name="saisie_notes_active" value="0">
                    <input type="checkbox" id="saisie_notes_active" name="saisie_notes_active" value="1" @checked(old('saisie_notes_active', $settings->saisie_notes_active))>
                    <label for="saisie_notes_active">
                        <span class="switch-titre">Saisie des notes</span>
                        <p class="switch-texte">Autorise les professeurs et la préfecture à encoder les cotes. Décochée, la saisie est refusée jusqu’à réactivation.</p>
                    </label>
                </div>
                <div class="switch-row">
                    <input type="hidden" name="portail_parents_actif" value="0">
                    <input type="checkbox" id="portail_parents_actif" name="portail_parents_actif" value="1" @checked(old('portail_parents_actif', $settings->portail_parents_actif))>
                    <label for="portail_parents_actif">
                        <span class="switch-titre">Portail Parents</span>
                        <p class="switch-texte">Ouvre aux parents l’accès aux notes, présences et annonces de leurs enfants.</p>
                    </label>
                </div>
                <div class="switch-row">
                    <input type="hidden" name="deliberation_active" value="0">
                    <input type="checkbox" id="deliberation_active" name="deliberation_active" value="1" @checked(old('deliberation_active', $settings->deliberation_active))>
                    <label for="deliberation_active">
                        <span class="switch-titre">Délibération &amp; passage de classe</span>
                        <p class="switch-texte">Active le module de délibération de fin d’année (seuils d’admission et de repêchage configurables dans Délibération).</p>
                    </label>
                </div>
                <div class="switch-row">
                    <input type="hidden" name="module_recus_actif" value="0">
                    <input type="checkbox" id="module_recus_actif" name="module_recus_actif" value="1" @checked(old('module_recus_actif', $settings->module_recus_actif ?? true))>
                    <label for="module_recus_actif">
                        <span class="switch-titre">Reçus de paiement</span>
                        <p class="switch-texte">Affiche la page imprimable du reçu après un encaissement. Décochée, la page reçu est fermée (404).</p>
                    </label>
                </div>
                <div class="switch-row">
                    <input type="hidden" name="module_recouvrement_actif" value="0">
                    <input type="checkbox" id="module_recouvrement_actif" name="module_recouvrement_actif" value="1" @checked(old('module_recouvrement_actif', $settings->module_recouvrement_actif ?? true))>
                    <label for="module_recouvrement_actif">
                        <span class="switch-titre">Recouvrement</span>
                        <p class="switch-texte">Ouvre le suivi des encaissements (classe, période, mois). Décochée, la page recouvrement est fermée (404).</p>
                    </label>
                </div>
                <div class="settings-hint">
                    <a href="{{ route('admin.modules.index') }}"><i class="fa fa-external-link"></i> Version courte de ces options (page « Commuter les modules »)</a>
                </div>
            </section>

            <section class="settings-panel">
                <h2 class="panel-title"><i class="fa fa-file-text-o"></i> Bulletin (en-tête, pied de page, règles)</h2>
                <p class="panel-hint">Ces valeurs sont reprises telles quelles sur chaque bulletin imprimé.</p>

                <h3 class="section-title">En-tête officiel</h3>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="bulletin_pays">Pays</label>
                        <input class="form-control" id="bulletin_pays" name="bulletin_pays" value="{{ old('bulletin_pays', $settings->bulletin_pays) }}" maxlength="100">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="bulletin_reference">Référence du formulaire</label>
                        <input class="form-control" id="bulletin_reference" name="bulletin_reference" value="{{ old('bulletin_reference', $settings->bulletin_reference) }}" maxlength="50">
                        <div class="settings-hint">Imprimée en bas de page (exemple : EBE/P.S./010).</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="bulletin_ministere">Ministère de tutelle</label>
                    <input class="form-control" id="bulletin_ministere" name="bulletin_ministere" value="{{ old('bulletin_ministere', $settings->bulletin_ministere) }}" maxlength="160">
                </div>
                <div class="form-group">
                    <label class="form-label" for="bulletin_titre">Titre du bulletin</label>
                    <input class="form-control" id="bulletin_titre" name="bulletin_titre" value="{{ old('bulletin_titre', $settings->bulletin_titre) }}" maxlength="160">
                    <div class="settings-hint">L’année scolaire est ajoutée automatiquement à la suite de ce titre.</div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="bulletin_province">Province</label>
                        <input class="form-control" id="bulletin_province" name="bulletin_province" value="{{ old('bulletin_province', $settings->bulletin_province) }}" maxlength="100">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="bulletin_ville">Ville</label>
                        <input class="form-control" id="bulletin_ville" name="bulletin_ville" value="{{ old('bulletin_ville', $settings->bulletin_ville) }}" maxlength="100">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="bulletin_commune">Commune / T.E.R.</label>
                        <input class="form-control" id="bulletin_commune" name="bulletin_commune" value="{{ old('bulletin_commune', $settings->bulletin_commune) }}" maxlength="100">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="bulletin_code_ecole">Code de l’école</label>
                        <input class="form-control" id="bulletin_code_ecole" name="bulletin_code_ecole" value="{{ old('bulletin_code_ecole', $settings->bulletin_code_ecole) }}" maxlength="50">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label" for="bulletin_lieu_signature">Lieu de signature</label>
                        <input class="form-control" id="bulletin_lieu_signature" name="bulletin_lieu_signature" value="{{ old('bulletin_lieu_signature', $settings->bulletin_lieu_signature) }}" maxlength="100">
                        <div class="settings-hint">Affiché dans « Fait à … ». Laisser vide pour laisser la ligne à remplir à la main.</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="bulletin_signataire">Fonction du signataire</label>
                    <input class="form-control" id="bulletin_signataire" name="bulletin_signataire" value="{{ old('bulletin_signataire', $settings->bulletin_signataire) }}" maxlength="120">
                    <div class="settings-hint">Exemple : Le Chef d’Établissement.</div>
                </div>

                <h3 class="section-title">Règles et mentions affichées</h3>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="form-label" for="bulletin_seuil_reussite">Seuil de réussite (%) *</label>
                        <input class="form-control" id="bulletin_seuil_reussite" type="number" step="0.01" min="0" max="100"
                               name="bulletin_seuil_reussite" value="{{ old('bulletin_seuil_reussite', $settings->bulletin_seuil_reussite ?? 50) }}" required>
                        <div class="settings-hint">En dessous de ce pourcentage général, l’élève double et les branches concernées sont listées pour le repêchage (50 % par défaut).</div>
                    </div>
                    <div class="col-md-8">
                        <div class="switch-row">
                            <input type="hidden" name="bulletin_afficher_place" value="0">
                            <input type="checkbox" id="bulletin_afficher_place" name="bulletin_afficher_place" value="1" @checked(old('bulletin_afficher_place', $settings->bulletin_afficher_place ?? true))>
                            <label for="bulletin_afficher_place">
                                <span class="switch-titre">Afficher la place dans la classe</span>
                                <p class="switch-texte">Ligne « PLACE / NBRE D’ÉLÈVES » du bulletin.</p>
                            </label>
                        </div>
                        <div class="switch-row">
                            <input type="hidden" name="bulletin_afficher_conduite" value="0">
                            <input type="checkbox" id="bulletin_afficher_conduite" name="bulletin_afficher_conduite" value="1" @checked(old('bulletin_afficher_conduite', $settings->bulletin_afficher_conduite ?? true))>
                            <label for="bulletin_afficher_conduite">
                                <span class="switch-titre">Afficher la conduite</span>
                                <p class="switch-texte">Ligne « CONDUITE » du bulletin.</p>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <div class="settings-actions">
                <a class="btn btn-secondary" href="{{ route('admin.profile.show') }}">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer les paramètres</button>
            </div>
        </form>
    </main>
</body>
</html>
