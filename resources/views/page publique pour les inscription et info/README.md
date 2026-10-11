# Partie publique (vitrine) — inscription, à propos, contact

**C'est ici que vivent les pages publiques.** Ce dossier contient :

```
accueil.blade.php        la page d'ouverture du système (/)
inscriptions.blade.php   conditions et démarches (/inscriptions)
apropos.blade.php        présentation de l'école (/a-propos)
contact.blade.php        coordonnées + formulaire (/contact)
layout.blade.php         en-tête, menu et pied de page communs
theme-source/            thème HTML de départ (« Start Bootstrap – Personal »), conservé comme
                         référence de design ; plus utilisé par l'application
```

Ces vues sont exposées sous le **namespace `site`** (`AppServiceProvider::boot()` →
`View::addNamespace('site', resource_path('views/page publique pour les inscription et info'))`),
donc appelées `view('site::accueil')` et héritées via `@extends('site::layout')`.

## Ce qui a été créé

| Élément | Chemin |
|---|---|
| Layout + 4 pages publiques | ce dossier : `layout.blade.php`, `accueil`, `inscriptions`, `apropos`, `contact` |
| Assets du thème servis par l'app | `public/site-assets/styles.css`, `public/site-assets/favicon.ico` |
| Contrôleur public | `app/Http/Controllers/PublicSiteController.php` |
| Écran admin des messages | `resources/views/admin/messages-contact/index.blade.php` + `AdminMessageContactController` |
| Table des messages | migration `2026_10_23_000000_create_contact_messages_table.php` (modèle `MessageContact`) |
| Tests | `tests/Feature/PublicSiteTest.php` |

`public/site-assets/` (et non `public/site/`) : un dossier physique `public/site/` entrerait en
conflit avec l'ancienne adresse `/site` — les requêtes renverraient 404 au lieu d'atteindre Laravel.

## Pages et routes publiques (aucun compte requis)

**La vitrine est la page d'ouverture du système.** `/` affiche l'accueil public ; la connexion a été
déplacée sur `/connexion` (le **nom de route `login` est conservé**, donc toutes les redirections,
middlewares et tests continuent de fonctionner).

| URL | Nom de route | Contenu |
|---|---|---|
| `/` | `site.accueil` | Page d'ouverture : présentation, modules de l'application, chiffres, étapes d'inscription, cycles, accès aux démarches |
| `/inscriptions` | `site.inscriptions` | Conditions d'admission, pièces à fournir, étapes, classes ouvertes, frais, FAQ |
| `/a-propos` | `site.apropos` | Mission, ambition, valeurs, organisation pédagogique, rôles, espace numérique |
| `/contact` | `site.contact` | Coordonnées, horaires, formulaire de contact |
| `POST /contact` | `site.contact.store` | Enregistrement du message (limité à 5 envois/minute + piège anti-robot) |
| `/connexion` | `login` | Écran de connexion (déplacé depuis `/`), avec retour à l'accueil |
| `/connexion-pin` | `login.pin` | Connexion par PIN |
| `/demande-inscription` | `registration.student.create` | Demande d'admission d'un élève (mêmes champs qu'avant) |
| `/demande-compte` | `registration.account.create` | Demande de compte pour le personnel / les parents |

Les anciennes adresses `/site`, `/site/inscriptions`, `/site/a-propos` et `/site/contact` répondent
par une redirection permanente (301) vers les nouvelles.

Les pages renvoient vers les démarches existantes : `/demande-inscription` et `/demande-compte`.

### Un seul design pour toutes les pages publiques

Toutes les pages hors session étendent `@extends('site::layout')` : menu, pied de page, palette et
styles de formulaire (cartes `.form-panel`, titres `.section-titre-form`, boutons arrondis) sont donc
identiques partout. Les vues concernées :

| Vue | Chemin |
|---|---|
| Demande d'inscription | `resources/views/registration/student-request.blade.php` |
| Demande de compte | `resources/views/registration/account-request.blade.php` |
| Connexion | `resources/views/projet/page-connexion/page-login-password.blade.php` |
| Connexion par PIN | `resources/views/projet/page-connexion/connexion-pin.blade.php` |

Pour ajouter une nouvelle page publique : créer un `*.blade.php` ici, l'étendre de `site::layout`
(`@section('titre')` + `@section('contenu')`) et déclarer sa route dans `routes/web.php`.

## Contenus

* Identité de l'école (nom, sigle, téléphone, e-mail, adresse) : reprise automatiquement de
  `ParametreSysteme` (`$systemSettings`, réglable dans **Administration → Profil → Paramètres**).
  ⚠️ Le champ *nom de l'établissement* contient actuellement « Espace de connexion pour
  l'administrateur » : remplacer cette valeur par le nom réel de l'école (ex. « Complexe scolaire
  Moty ») pour que la vitrine affiche le bon nom.
* Cycles, classes ouvertes, effectif inscrit et année en cours : lus en base, pas de saisie manuelle.
* Textes rédactionnels (conditions d'admission, pièces, frais, historique, horaires, FAQ) : écrits
  dans les `*.blade.php` de ce dossier — à ajuster selon la réalité de l'établissement,
  notamment les horaires du secrétariat sur `/contact` et le paragraphe « frais scolaires ».

## Messages de contact côté administration

`Administration → Messages de contact` (`/admin/messages-contact`) : filtre Tous / Nouveaux / Traités,
réponse par e-mail pré-remplie, marquage comme traité (avec horodatage et auteur), réouverture et
suppression. Un compteur de messages nouveaux apparaît dans la barre latérale de l'administration.

## Vérification

```bash
php artisan test --filter=PublicSiteTest   # 9 tests
```
