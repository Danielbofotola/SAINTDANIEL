<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\EleveInscriptionController;
use App\Http\Controllers\InscriptionAttenteController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ProfesseurController;
use App\Http\Controllers\ProfesseurDashboardController;
use App\Http\Controllers\ProfesseurEspaceController;
use App\Http\Controllers\ProfesseurEvaluationController;
use App\Http\Controllers\ProfesseurPresenceController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessagerieController;
use App\Http\Controllers\AffectationCoursController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\EmploiDuTempsController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\FraisController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\ComptabiliteController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\ArchiveAnneeController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\ModuleSwitchController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\JournalDecisionController;
use App\Http\Controllers\FluxAcademiqueController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ComptablePaiementController;
use App\Http\Controllers\ComptableProfilController;
use App\Http\Controllers\ComptableRecouvrementController;
use App\Http\Controllers\ComptableDepenseController;
use App\Http\Controllers\RecuController;
use App\Http\Controllers\SectionOptionController;
use App\Http\Controllers\RegistrationRequestController;
use App\Http\Controllers\AdminRegistrationRequestController;
use App\Http\Controllers\AdminMessageContactController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\ParametreSystemeController;
use App\Http\Controllers\AnneeScolaireController;
use App\Http\Controllers\ComptableDashboardController;
use App\Http\Controllers\TitulaireDashboardController;
use App\Http\Controllers\TitulaireBulletinController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrefectureDashboardController;
use App\Http\Controllers\PrefectureDisciplineController;
use App\Http\Controllers\PrefectureInscriptionController;
use App\Http\Controllers\PortailParentController;
use App\Http\Controllers\AppelController;
use App\Http\Controllers\DeliberationController;
use App\Http\Controllers\AdminMessagerieController;

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.store');
    Route::get('/connexion-pin', [AuthController::class, 'showPinForm'])->name('login.pin');
    Route::post('/connexion-pin', [AuthController::class, 'loginWithPin'])->name('login.pin.store');
});

Route::get('/demande-inscription', [RegistrationRequestController::class, 'createStudentRequest'])->name('registration.student.create');
Route::post('/demande-inscription', [RegistrationRequestController::class, 'storeStudentRequest'])->middleware('throttle:5,1')->name('registration.student.store');
Route::get('/demande-compte', [RegistrationRequestController::class, 'createAccountRequest'])->name('registration.account.create');
Route::post('/demande-compte', [RegistrationRequestController::class, 'storeAccountRequest'])->middleware('throttle:5,1')->name('registration.account.store');
    
// Partie publique (vitrine) : c'est la page d'ouverture du système.
Route::get('/', [PublicSiteController::class, 'accueil'])->name('site.accueil');
Route::get('/inscriptions', [PublicSiteController::class, 'inscriptions'])->name('site.inscriptions');
Route::get('/a-propos', [PublicSiteController::class, 'apropos'])->name('site.apropos');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->middleware('throttle:5,1')->name('site.contact.store');

// Page d'amorçage : création du PREMIER compte administrateur (se ferme dès qu'un admin existe).
Route::get('/installation-secrete', [InstallationController::class, 'show'])->middleware('throttle:10,1')->name('systeme.installation');
Route::post('/installation-secrete', [InstallationController::class, 'store'])->middleware('throttle:6,1')->name('systeme.installation.store');
    
// Anciennes adresses de la vitrine : redirections permanentes.
Route::permanentRedirect('/site', '/');
Route::permanentRedirect('/site/inscriptions', '/inscriptions');
Route::permanentRedirect('/site/a-propos', '/a-propos');
Route::permanentRedirect('/site/contact', '/contact');

Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/accueil', [HomeController::class, 'index'])->name('home');
    Route::get('/mon-profil', [UserProfileController::class, 'showPersonalProfile'])->name('profile.show');
    // Modification de son propre profil : réservée à l'administrateur. Les autres
    // rôles consultent leur fiche (profile.show) mais ne peuvent pas la modifier.
    Route::get('/mon-profil/modifier', [UserProfileController::class, 'editPersonalProfile'])->middleware('role:admin')->name('profile.edit');
    Route::put('/mon-profil', [UserProfileController::class, 'update'])->middleware('role:admin')->name('profile.update');
    Route::get('/admin/tableau-de-bord', [AdminDashboardController::class, 'index'])->middleware('role:admin')->name('admin.dashboard');
    Route::get('/admin/professeurs/nouveau', [ProfesseurController::class, 'create'])->middleware('role:admin')->name('admin.professeurs.create');
    Route::post('/admin/professeurs', [ProfesseurController::class, 'store'])->middleware('role:admin')->name('admin.professeurs.store');
    Route::get('/admin/professeurs', [ProfesseurController::class, 'index'])->middleware('role:admin')->name('admin.professeurs.index');
    Route::get('/admin/professeurs/{professeur}', [ProfesseurController::class, 'show'])->middleware('role:admin')->name('admin.professeurs.show');
    Route::get('/admin/professeurs/{professeur}/carte', [ProfesseurController::class, 'carte'])->middleware('role:admin')->name('admin.professeurs.carte');
    Route::get('/admin/professeurs/{professeur}/emploi-du-temps', [ProfesseurController::class, 'emploiDuTemps'])->middleware('role:admin')->name('admin.professeurs.emploi-du-temps');
    Route::get('/admin/professeurs/{professeur}/modifier', [ProfesseurController::class, 'edit'])->middleware('role:admin')->name('admin.professeurs.edit');
    Route::put('/admin/professeurs/{professeur}', [ProfesseurController::class, 'update'])->middleware('role:admin')->name('admin.professeurs.update');
    Route::post('/admin/professeurs/{professeur}/documents', [ProfesseurController::class, 'storeDocument'])->middleware('role:admin')->name('admin.professeurs.documents.store');
    Route::delete('/admin/professeurs/documents/{document}', [ProfesseurController::class, 'destroyDocument'])->middleware('role:admin')->name('admin.professeurs.documents.destroy');
    Route::patch('/admin/professeurs/{professeur}/statut', [ProfesseurController::class, 'toggleStatus'])->middleware('role:admin')->name('admin.professeurs.toggle-status');
    // Module « Mutation » : affectations des professeurs (créer, muter, permuter, supprimer).
    // Piloté par la matrice (feature « affectations ») — le Préfet (niveau « oui ») contrôle tout.
    Route::get('/admin/cours-affectations', [AffectationCoursController::class, 'index'])->middleware('permission:affectations')->name('admin.affectations.index');
    Route::post('/admin/affectations', [AffectationCoursController::class, 'store'])->middleware('permission:affectations')->name('admin.affectations.store');
    Route::patch('/admin/affectations/{affectation}/muter', [AffectationCoursController::class, 'muter'])->middleware('permission:affectations')->name('admin.affectations.muter');
    Route::post('/admin/affectations/permuter', [AffectationCoursController::class, 'permuter'])->middleware('permission:affectations')->name('admin.affectations.permuter');
    Route::delete('/admin/affectations/{affectation}', [AffectationCoursController::class, 'destroy'])->middleware('permission:affectations')->name('admin.affectations.destroy');
    Route::get('/admin/cours', [CoursController::class, 'index'])->middleware('role:admin')->name('admin.cours.index');
    Route::post('/admin/cours', [CoursController::class, 'store'])->middleware('role:admin')->name('admin.cours.store');
    Route::put('/admin/cours/{cours}', [CoursController::class, 'update'])->middleware('role:admin')->name('admin.cours.update');
    Route::patch('/admin/cours/{cours}/statut', [CoursController::class, 'toggleStatus'])->middleware('role:admin')->name('admin.cours.toggle-status');
    Route::delete('/admin/cours/{cours}', [CoursController::class, 'destroy'])->middleware('role:admin')->name('admin.cours.destroy');
    // Emplois du temps : pilotés par la matrice (feature « emplois_du_temps ») —
    // le Préfet (niveau « oui ») peut les consulter, les créer et les supprimer.
    Route::get('/admin/emplois-du-temps', [EmploiDuTempsController::class, 'index'])->middleware('permission:emplois_du_temps')->name('admin.emplois-du-temps.index');
    Route::post('/admin/emplois-du-temps', [EmploiDuTempsController::class, 'store'])->middleware('permission:emplois_du_temps')->name('admin.emplois-du-temps.store');
    Route::delete('/admin/emplois-du-temps/{emploiDuTemps}', [EmploiDuTempsController::class, 'destroy'])->middleware('permission:emplois_du_temps')->name('admin.emplois-du-temps.destroy');
    Route::get('/admin/periodes', [PeriodeController::class, 'index'])->middleware('role:admin')->name('admin.periodes.index');
    Route::post('/admin/periodes', [PeriodeController::class, 'store'])->middleware('role:admin')->name('admin.periodes.store');
    Route::patch('/admin/periodes/{periode}/statut', [PeriodeController::class, 'toggleStatus'])->middleware('role:admin')->name('admin.periodes.toggle-status');
    Route::delete('/admin/periodes/{periode}', [PeriodeController::class, 'destroy'])->middleware('role:admin')->name('admin.periodes.destroy');
    Route::get('/admin/evaluations', [EvaluationController::class, 'index'])->middleware('role:admin')->name('admin.evaluations.index');
    Route::post('/admin/evaluations', [EvaluationController::class, 'store'])->middleware('role:admin')->name('admin.evaluations.store');
    Route::get('/admin/evaluations/{evaluation}/notes', [EvaluationController::class, 'notes'])->middleware('role:admin')->name('admin.evaluations.notes');
    Route::post('/admin/evaluations/{evaluation}/notes', [EvaluationController::class, 'storeNotes'])->middleware('role:admin')->name('admin.evaluations.notes.store');
    Route::delete('/admin/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->middleware('role:admin')->name('admin.evaluations.destroy');
    Route::get('/admin/notes', [NotesController::class, 'index'])->middleware('role:admin')->name('admin.notes.index');
    Route::post('/admin/notes', [NotesController::class, 'store'])->middleware('role:admin')->name('admin.notes.store');
    Route::get('/admin/bulletins', [BulletinController::class, 'index'])->middleware('role:admin')->name('admin.bulletins.index');
    Route::post('/admin/bulletins/generer', [BulletinController::class, 'generate'])->middleware('role:admin')->name('admin.bulletins.generate');
    Route::get('/admin/bulletins/{inscription}/document', [BulletinController::class, 'document'])->middleware('role:admin')->name('admin.bulletins.document');
    Route::get('/admin/frais', [FraisController::class, 'index'])->middleware('role:admin')->name('admin.frais.index');
    Route::post('/admin/frais/types', [FraisController::class, 'storeType'])->middleware('role:admin')->name('admin.frais.types.store');
    Route::patch('/admin/frais/types/{typeFrais}/statut', [FraisController::class, 'toggleType'])->middleware('role:admin')->name('admin.frais.types.toggle-status');
    Route::delete('/admin/frais/types/{typeFrais}', [FraisController::class, 'destroyType'])->middleware('role:admin')->name('admin.frais.types.destroy');
    Route::post('/admin/frais/tarifs', [FraisController::class, 'storeTarif'])->middleware('role:admin')->name('admin.frais.tarifs.store');
    Route::delete('/admin/frais/tarifs/{fraisScolaire}', [FraisController::class, 'destroyTarif'])->middleware('role:admin')->name('admin.frais.tarifs.destroy');
    Route::get('/admin/paiements', [PaiementController::class, 'index'])->middleware('role:admin')->name('admin.paiements.index');
    Route::post('/admin/paiements', [PaiementController::class, 'store'])->middleware('role:admin')->name('admin.paiements.store');
    Route::delete('/admin/paiements/{paiement}', [PaiementController::class, 'destroy'])->middleware('role:admin')->name('admin.paiements.destroy');
    Route::get('/admin/paiements/{paiement}/recu', [RecuController::class, 'show'])->middleware('role:admin')->name('admin.paiements.recu');
    Route::get('/admin/depenses', [DepenseController::class, 'index'])->middleware('role:admin')->name('admin.depenses.index');
    Route::post('/admin/depenses', [DepenseController::class, 'store'])->middleware('role:admin')->name('admin.depenses.store');
    Route::post('/admin/depenses/categories', [DepenseController::class, 'storeCategorie'])->middleware('role:admin')->name('admin.depenses.categories.store');
    Route::delete('/admin/depenses/categories/{categorieDepense}', [DepenseController::class, 'destroyCategorie'])->middleware('role:admin')->name('admin.depenses.categories.destroy');
    Route::delete('/admin/depenses/{depense}', [DepenseController::class, 'destroy'])->middleware('role:admin')->name('admin.depenses.destroy');
    Route::get('/admin/paies', [PaieController::class, 'index'])->middleware('role:admin')->name('admin.paies.index');
    Route::post('/admin/paies/salaires', [PaieController::class, 'storeSalaire'])->middleware('role:admin')->name('admin.paies.salaires.store');
    Route::post('/admin/paies', [PaieController::class, 'storePaie'])->middleware('role:admin')->name('admin.paies.store');
    Route::delete('/admin/paies/{paie}', [PaieController::class, 'destroyPaie'])->middleware('role:admin')->name('admin.paies.destroy');
    Route::get('/admin/comptabilite', [ComptabiliteController::class, 'index'])->middleware('role:admin')->name('admin.comptabilite.index');
    Route::post('/admin/comptabilite/synchroniser', [ComptabiliteController::class, 'synchroniser'])->middleware('role:admin')->name('admin.comptabilite.synchroniser');
    Route::post('/admin/comptabilite/ecritures', [ComptabiliteController::class, 'storeEcriture'])->middleware('role:admin')->name('admin.comptabilite.ecritures.store');
    Route::delete('/admin/comptabilite/ecritures/{ecriture}', [ComptabiliteController::class, 'destroyEcriture'])->middleware('role:admin')->name('admin.comptabilite.ecritures.destroy');
    Route::get('/admin/comptabilite/plan', [ComptabiliteController::class, 'plan'])->middleware('role:admin')->name('admin.comptabilite.plan');
    Route::post('/admin/comptabilite/plan', [ComptabiliteController::class, 'storeCompte'])->middleware('role:admin')->name('admin.comptabilite.plan.store');
    Route::delete('/admin/comptabilite/plan/{compte}', [ComptabiliteController::class, 'destroyCompte'])->middleware('role:admin')->name('admin.comptabilite.plan.destroy');
    Route::get('/admin/comptabilite/balance', [ComptabiliteController::class, 'balance'])->middleware('role:admin')->name('admin.comptabilite.balance');
    Route::get('/admin/statistiques', [StatistiquesController::class, 'index'])->middleware('role:admin')->name('admin.statistiques.index');
    Route::get('/admin/statistiques/evolution', [StatistiquesController::class, 'evolution'])->middleware('role:admin')->name('admin.statistiques.evolution');
    Route::get('/admin/archives', [ArchiveAnneeController::class, 'index'])->middleware('role:admin')->name('admin.archives.index');
    Route::get('/admin/archives/{archiveAnnee}', [ArchiveAnneeController::class, 'show'])->middleware('role:admin')->name('admin.archives.show');
    Route::post('/admin/archives', [ArchiveAnneeController::class, 'store'])->middleware('role:admin')->name('admin.archives.store');
    Route::delete('/admin/archives/{archiveAnnee}', [ArchiveAnneeController::class, 'destroy'])->middleware('role:admin')->name('admin.archives.destroy');
    Route::get('/admin/cycles', [CycleController::class, 'index'])->middleware('role:admin')->name('admin.cycles.index');
    Route::post('/admin/cycles', [CycleController::class, 'store'])->middleware('role:admin')->name('admin.cycles.store');
    Route::patch('/admin/cycles/{cycle}', [CycleController::class, 'update'])->middleware('role:admin')->name('admin.cycles.update');
    // Activation d'un cycle : pilotée par la matrice des permissions (feature « structures »).
    Route::patch('/admin/cycles/{cycle}/statut', [CycleController::class, 'toggleStatus'])->middleware('permission:structures')->name('admin.cycles.toggle-status');
    Route::delete('/admin/cycles/{cycle}', [CycleController::class, 'destroy'])->middleware('role:admin')->name('admin.cycles.destroy');
    Route::get('/admin/modules', [ModuleSwitchController::class, 'index'])->middleware('role:admin', 'permission:modules')->name('admin.modules.index');
    Route::put('/admin/modules', [ModuleSwitchController::class, 'update'])->middleware('role:admin', 'permission:modules')->name('admin.modules.update');
    Route::get('/admin/permissions', [PermissionController::class, 'index'])->middleware('role:admin')->name('admin.permissions.index');
    Route::get('/admin/audit', [JournalDecisionController::class, 'audit'])->middleware('role:admin,systeme', 'permission:audit')->name('admin.audit.index');
    // Poste de travail des rôles pilotés par la matrice des permissions.
    Route::get('/poste', [PosteController::class, 'show'])->middleware('role:systeme,directeur_etudes,secretaire,disciplinaire')->name('poste.show');
    Route::get('/admin/profil', [UserProfileController::class, 'show'])->middleware('role:admin')->name('admin.profile.show');
    Route::get('/admin/profil/parametres', [ParametreSystemeController::class, 'edit'])->middleware('role:admin')->name('admin.profile.settings');
    Route::put('/admin/profil/parametres', [ParametreSystemeController::class, 'update'])->middleware('role:admin')->name('admin.profile.settings.update');
    // Validation des demandes d'inscription : pilotée par la matrice (feature « inscriptions »).
    Route::get('/admin/demandes/inscriptions', [AdminRegistrationRequestController::class, 'studentRequests'])->middleware('permission:inscriptions')->name('admin.registration.students');
    Route::get('/admin/demandes/inscriptions/historique', [AdminRegistrationRequestController::class, 'studentHistory'])->middleware('permission:inscriptions')->name('admin.registration.students.history');
    Route::post('/admin/demandes/inscriptions/{demandeInscription}/accepterta', [AdminRegistrationRequestController::class, 'approveStudent'])->middleware('permission:inscriptions')->name('admin.registration.students.approve');
    Route::post('/admin/demandes/inscriptions/{demandeInscription}/refuser', [AdminRegistrationRequestController::class, 'rejectStudent'])->middleware('permission:inscriptions')->name('admin.registration.students.reject');
    Route::get('/admin/demandes/comptes', [AdminRegistrationRequestController::class, 'accountRequests'])->middleware('role:admin')->name('admin.registration.accounts');
    Route::post('/admin/demandes/comptes/{demandeCompte}/accepter', [AdminRegistrationRequestController::class, 'approveAccount'])->middleware('role:admin')->name('admin.registration.accounts.approve');
    Route::post('/admin/demandes/comptes/{demandeCompte}/refuser', [AdminRegistrationRequestController::class, 'rejectAccount'])->middleware('role:admin')->name('admin.registration.accounts.reject');
    Route::get('/admin/messages-contact', [AdminMessageContactController::class, 'index'])->middleware('role:admin')->name('admin.messages-contact.index');
    Route::patch('/admin/messages-contact/{messageContact}/statut', [AdminMessageContactController::class, 'updateStatus'])->middleware('role:admin')->name('admin.messages-contact.statut');
    Route::delete('/admin/messages-contact/{messageContact}', [AdminMessageContactController::class, 'destroy'])->middleware('role:admin')->name('admin.messages-contact.destroy');
    Route::get('/admin/profil/statistiques', [UserProfileController::class, 'statistics'])->middleware('role:admin')->name('admin.profile.statistics');
    Route::get('/admin/utilisateurs-en-ligne', [UserProfileController::class, 'online'])->middleware('role:admin')->name('admin.profile.online');
    // Notifications : boîte de réception commune à tous les rôles.
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('notifications.index');
    Route::patch('/notifications/toutes-lues', [NotificationController::class, 'readAll'])->middleware('auth')->name('notifications.readall');
    Route::patch('/notifications/{notification}/lue', [NotificationController::class, 'read'])->middleware('auth')->name('notifications.read');
    // Messagerie interne : discussion directe ou diffusion à tous les utilisateurs.
    Route::get('/messagerie', [MessagerieController::class, 'index'])->middleware('auth')->name('messagerie.index');
    Route::post('/messagerie', [MessagerieController::class, 'store'])->middleware('auth')->name('messagerie.store');
    Route::post('/messagerie/{conversation}/messages', [MessagerieController::class, 'repondre'])->middleware('auth')->name('messagerie.repondre');
    Route::get('/messagerie/{conversation}/messages', [MessagerieController::class, 'messages'])->middleware('auth')->name('messagerie.messages');
    Route::post('/messagerie/demarrer', [MessagerieController::class, 'demarrer'])->middleware('auth')->name('messagerie.demarrer');
    Route::post('/messagerie/messages/{message}/reaction', [MessagerieController::class, 'reagir'])->middleware('auth')->name('messagerie.reagir');
    // Suppression « pour soi » : la discussion quitte la liste de l'utilisateur,
    // les autres participants conservent tous les messages.
    Route::delete('/messagerie/{conversation}', [MessagerieController::class, 'supprimer'])->middleware('auth')->name('messagerie.supprimer');
    // Appels audio/vidéo (signalisation WebRTC, sans serveur temps réel).
    Route::post('/appels', [AppelController::class, 'store'])->middleware('auth')->name('appels.store');
    Route::get('/appels/entrant', [AppelController::class, 'entrant'])->middleware('auth')->name('appels.entrant');
    Route::get('/appels/{appel}', [AppelController::class, 'show'])->middleware('auth')->name('appels.show');
    Route::post('/appels/{appel}/reponse', [AppelController::class, 'repondre'])->middleware('auth')->name('appels.reponse');
    Route::post('/appels/{appel}/refuser', [AppelController::class, 'refuser'])->middleware('auth')->name('appels.refuser');
    Route::post('/appels/{appel}/terminer', [AppelController::class, 'terminer'])->middleware('auth')->name('appels.terminer');
    Route::post('/appels/{appel}/ice', [AppelController::class, 'ice'])->middleware('auth')->name('appels.ice');
    // Aperçu du profil d'un correspondant (depuis la messagerie).
    Route::get('/utilisateurs/{user}', [UserProfileController::class, 'apercu'])->middleware('auth')->name('utilisateurs.apercu');
    // Délibération de fin d'année : critères, décisions et passage en masse.
    Route::get('/admin/deliberations', [DeliberationController::class, 'index'])->middleware('role:admin')->name('admin.deliberations.index');
    Route::put('/admin/deliberations/criteres', [DeliberationController::class, 'criteres'])->middleware('role:admin')->name('admin.deliberations.criteres');
    Route::post('/admin/deliberations/decisions', [DeliberationController::class, 'enregistrer'])->middleware('role:admin')->name('admin.deliberations.enregistrer');
    Route::post('/admin/deliberations/appliquer', [DeliberationController::class, 'appliquer'])->middleware('role:admin')->name('admin.deliberations.appliquer');
    // Modération de la messagerie : voir toutes les conversations et supprimer.
    Route::get('/admin/messagerie', [AdminMessagerieController::class, 'index'])->middleware('role:admin')->name('admin.messagerie.index');
    Route::get('/admin/messagerie/{conversation}', [AdminMessagerieController::class, 'show'])->middleware('role:admin')->name('admin.messagerie.show');
    Route::delete('/admin/messagerie/{conversation}', [AdminMessagerieController::class, 'destroy'])->middleware('role:admin')->name('admin.messagerie.destroy');
    Route::delete('/admin/messagerie/messages/{message}', [AdminMessagerieController::class, 'destroyMessage'])->middleware('role:admin')->name('admin.messagerie.messages.destroy');
    // Annonces : diffusion d'une notification par l'administrateur.
    Route::get('/admin/annonces', [AnnonceController::class, 'index'])->middleware('role:admin')->name('admin.annonces.index');
    Route::post('/admin/annonces', [AnnonceController::class, 'store'])->middleware('role:admin')->name('admin.annonces.store');
    Route::delete('/admin/annonces/{annonce}', [AnnonceController::class, 'destroy'])->middleware('role:admin')->name('admin.annonces.destroy');
    Route::get('/admin/comptes', [UserProfileController::class, 'accounts'])->middleware('role:admin')->name('admin.profile.accounts');
    Route::get('/admin/comptes/{user}/modifier', [UserProfileController::class, 'editAccount'])->middleware('role:admin')->name('admin.profile.accounts.edit');
    Route::put('/admin/comptes/{user}', [UserProfileController::class, 'updateAccount'])->middleware('role:admin')->name('admin.profile.accounts.update');
    Route::post('/admin/comptes/{user}/impersoner', [UserProfileController::class, 'impersonate'])->middleware('role:admin')->name('admin.profile.accounts.impersonate');
    Route::get('/admin/comptes/nouveau', [UserProfileController::class, 'createAccount'])->middleware('role:admin')->name('admin.profile.accounts.create');
    Route::post('/admin/comptes', [UserProfileController::class, 'storeAccount'])->middleware('role:admin')->name('admin.profile.accounts.store');
    Route::get('/admin/profil/modifier', [UserProfileController::class, 'edit'])->middleware('role:admin')->name('admin.profile.edit');
    Route::put('/admin/profil', [UserProfileController::class, 'update'])->middleware('role:admin')->name('admin.profile.update');
    Route::patch('/admin/profil/mot-de-passe', [UserProfileController::class, 'updatePassword'])->middleware('role:admin')->name('admin.profile.password');
    Route::patch('/admin/profil/desactiver', [UserProfileController::class, 'deactivate'])->middleware('role:admin')->name('admin.profile.deactivate');
    Route::patch('/admin/comptes/{user}/statut', [UserProfileController::class, 'toggleAccount'])->middleware('role:admin')->name('admin.profile.accounts.toggle');
    Route::get('/admin/comptes/{user}/mot-de-passe', [UserProfileController::class, 'editAccountPassword'])->middleware('role:admin')->name('admin.profile.accounts.password.edit');
    Route::patch('/admin/comptes/{user}/mot-de-passe', [UserProfileController::class, 'updateAccountPassword'])->middleware('role:admin')->name('admin.profile.accounts.password.update');
    Route::delete('/admin/comptes/{user}', [UserProfileController::class, 'destroyAccount'])->middleware('role:admin')->name('admin.profile.accounts.destroy');
    Route::post('/fin-impersonation', [UserProfileController::class, 'stopImpersonation'])->name('admin.impersonation.stop');
    Route::get('/admin/eleves', [EleveController::class, 'index'])->middleware('role:admin')->name('admin.eleves.index');
    // Pages dédiées AVANT la route « {eleve} » (un seul segment) pour ne pas être avalées.
    Route::get('/admin/eleves/desactives', [EleveController::class, 'desactives'])->middleware('role:admin')->name('admin.eleves.desactives');
    Route::get('/admin/eleves/archives', [EleveController::class, 'archives'])->middleware('role:admin')->name('admin.eleves.archives');
    // Modification du dossier élève : pilotée par la matrice (feature « inscriptions ») —
    // le Préfet (niveau « oui ») peut corriger un dossier.
    Route::get('/admin/eleves/{eleve}/modifier', [EleveController::class, 'edit'])->middleware('permission:inscriptions')->name('admin.eleves.edit');
    Route::put('/admin/eleves/{eleve}', [EleveController::class, 'update'])->middleware('permission:inscriptions')->name('admin.eleves.update');
    Route::patch('/admin/eleves/{eleve}/statut', [EleveController::class, 'toggleStatut'])->middleware('role:admin')->name('admin.eleves.toggle');
    Route::post('/admin/eleves/{eleve}/documents', [EleveController::class, 'storeDocument'])->middleware('permission:inscriptions')->name('admin.eleves.documents.store');
    Route::get('/admin/eleves/{eleve}', [EleveController::class, 'show'])->middleware('role:admin')->name('admin.eleves.show');
    Route::get('/admin/eleves/{eleve}/carte', [EleveController::class, 'carte'])->middleware('role:admin')->name('admin.eleves.carte');
    Route::get('/admin/eleves/inscription/nouvelle', [EleveInscriptionController::class, 'create'])->middleware('role:admin')->name('admin.eleves.create');
    Route::post('/admin/eleves/inscription', [EleveInscriptionController::class, 'store'])->middleware('role:admin')->name('admin.eleves.store');
    // Inscriptions restées « en attente » (jamais validées par la caisse) : nettoyage.
    Route::get('/admin/inscriptions/attente', [InscriptionAttenteController::class, 'index'])->middleware('role:admin')->name('admin.inscriptions.attente');
    Route::delete('/admin/inscriptions/attente/{inscription}', [InscriptionAttenteController::class, 'destroy'])->middleware('role:admin')->name('admin.inscriptions.attente.destroy');
    Route::delete('/admin/inscriptions/attente', [InscriptionAttenteController::class, 'purge'])->middleware('role:admin')->name('admin.inscriptions.attente.purge');
    Route::get('/admin/salles', [SalleController::class, 'index'])->middleware('role:admin')->name('admin.salles.index');
    Route::get('/admin/annees', [AnneeScolaireController::class, 'index'])->middleware('role:admin')->name('admin.annees.index');
    Route::post('/admin/annees', [AnneeScolaireController::class, 'store'])->middleware('role:admin')->name('admin.annees.store');
    Route::get('/admin/annees/{annee}/modifier', [AnneeScolaireController::class, 'edit'])->middleware('role:admin')->name('admin.annees.edit');
    Route::put('/admin/annees/{annee}', [AnneeScolaireController::class, 'update'])->middleware('role:admin')->name('admin.annees.update');
    Route::delete('/admin/annees/{annee}', [AnneeScolaireController::class, 'destroy'])->middleware('role:admin')->name('admin.annees.destroy');
    Route::patch('/admin/annees/{annee}/statut', [AnneeScolaireController::class, 'toggle'])->middleware('role:admin')->name('admin.annees.toggle');
    Route::get('/admin/salles/nouvelle', [SalleController::class, 'create'])->middleware('role:admin')->name('admin.salles.create');
    Route::post('/admin/salles', [SalleController::class, 'store'])->middleware('role:admin')->name('admin.salles.store');
    Route::get('/admin/classes/nouvelle', [ClasseController::class, 'create'])->middleware('role:admin')->name('admin.classes.create');
    Route::post('/admin/classes', [ClasseController::class, 'store'])->middleware('role:admin')->name('admin.classes.store');
    Route::get('/admin/sections', [SectionOptionController::class, 'indexSections'])->middleware('role:admin')->name('admin.sections.index');
    Route::get('/admin/sections/nouvelle', [SectionOptionController::class, 'createSection'])->middleware('role:admin')->name('admin.sections.create');
    Route::post('/admin/sections', [SectionOptionController::class, 'storeSection'])->middleware('role:admin')->name('admin.sections.store');
    Route::get('/admin/options', [SectionOptionController::class, 'indexOptions'])->middleware('role:admin')->name('admin.options.index');
    Route::get('/admin/options/nouvelle', [SectionOptionController::class, 'createOption'])->middleware('role:admin')->name('admin.options.create');
    Route::post('/admin/options', [SectionOptionController::class, 'storeOption'])->middleware('role:admin')->name('admin.options.store');
    Route::get('/admin/classes', [ClasseController::class, 'index'])->middleware('role:admin')->name('admin.classes.index');
    Route::get('/admin/classes/{classe}/eleves', [ClasseController::class, 'students'])->middleware('role:admin')->name('admin.classes.students');
    Route::get('/admin/classes/{classe}/modifier', [ClasseController::class, 'edit'])->middleware('role:admin')->name('admin.classes.edit');
    Route::put('/admin/classes/{classe}', [ClasseController::class, 'update'])->middleware('role:admin')->name('admin.classes.update');
    // Activation d'une classe : pilotée par la matrice des permissions (feature « structures »).
    Route::patch('/admin/classes/{classe}/statut', [ClasseController::class, 'toggleStatus'])->middleware('permission:structures')->name('admin.classes.toggle-status');
    Route::delete('/admin/classes/{classe}', [ClasseController::class, 'destroy'])->middleware('role:admin')->name('admin.classes.destroy');
    Route::get('/admin/salles/{salle}/modifier', [SalleController::class, 'edit'])->middleware('role:admin')->name('admin.salles.edit');
    Route::put('/admin/salles/{salle}', [SalleController::class, 'update'])->middleware('role:admin')->name('admin.salles.update');
    Route::patch('/admin/salles/{salle}/statut', [SalleController::class, 'toggleStatus'])->middleware('role:admin')->name('admin.salles.toggle-status');
    Route::delete('/admin/salles/{salle}', [SalleController::class, 'destroy'])->middleware('role:admin')->name('admin.salles.destroy');
    Route::get('/prefecture/tableau-de-bord', [PrefectureDashboardController::class, 'index'])->middleware('role:prefetture')->name('prefecture.dashboard');
    Route::get('/prefecture/effectifs', [PrefectureDashboardController::class, 'effectifs'])->middleware('role:prefetture')->name('prefecture.effectifs');
    Route::get('/prefecture/classes', [PrefectureDashboardController::class, 'classes'])->middleware('role:prefetture')->name('prefecture.classes');
    Route::get('/prefecture/personnel', [PrefectureDashboardController::class, 'personnel'])->middleware('role:prefetture')->name('prefecture.personnel');
    Route::get('/prefecture/profil', [PrefectureDashboardController::class, 'profil'])->middleware('role:prefetture')->name('prefecture.profil');
    // Vie scolaire : présence, horaires, évaluations, dossiers élèves, tuteurs.
    Route::get('/prefecture/presences', [PrefectureDashboardController::class, 'presences'])->middleware('role:prefetture')->name('prefecture.presences');
    Route::get('/prefecture/horaires', [PrefectureDashboardController::class, 'horaires'])->middleware('role:prefetture')->name('prefecture.horaires');
    Route::get('/prefecture/evaluations', [PrefectureDashboardController::class, 'evaluations'])->middleware('role:prefetture')->name('prefecture.evaluations');
    Route::get('/prefecture/eleves', [PrefectureDashboardController::class, 'eleves'])->middleware('role:prefetture')->name('prefecture.eleves');
    Route::get('/prefecture/eleves/{eleve}', [PrefectureDashboardController::class, 'eleveFiche'])->middleware('role:prefetture')->name('prefecture.eleves.fiche');
    Route::get('/prefecture/tuteurs', [PrefectureDashboardController::class, 'tuteurs'])->middleware('role:prefetture')->name('prefecture.tuteurs');
    // Discipline : registre des sanctions (préfet et poste « disciplinaire »).
    Route::get('/prefecture/discipline', [PrefectureDisciplineController::class, 'index'])->middleware('role:prefetture,disciplinaire', 'permission:discipline')->name('prefecture.discipline.index');
    Route::post('/prefecture/discipline', [PrefectureDisciplineController::class, 'store'])->middleware('role:prefetture,disciplinaire', 'permission:discipline')->name('prefecture.discipline.store');
    Route::patch('/prefecture/discipline/{sanction}/lever', [PrefectureDisciplineController::class, 'lever'])->middleware('role:prefetture,disciplinaire', 'permission:discipline')->name('prefecture.discipline.lever');
    Route::delete('/prefecture/discipline/{sanction}', [PrefectureDisciplineController::class, 'destroy'])->middleware('role:prefetture,disciplinaire', 'permission:discipline')->name('prefecture.discipline.destroy');
    // Validation des demandes d'inscription par le Préfet — ouverte seulement si
    // l'administration l'y a autorisé (réglage « prefet_valide_inscriptions »).
    Route::get('/prefecture/demandes-inscription', [PrefectureInscriptionController::class, 'index'])->middleware('role:prefetture', 'permission:inscriptions')->name('prefecture.demandes.inscriptions');
    Route::post('/prefecture/demandes-inscription/{demandeInscription}/accepter', [PrefectureInscriptionController::class, 'approve'])->middleware('role:prefetture', 'permission:inscriptions')->name('prefecture.demandes.inscriptions.approve');
    Route::post('/prefecture/demandes-inscription/{demandeInscription}/refuser', [PrefectureInscriptionController::class, 'reject'])->middleware('role:prefetture', 'permission:inscriptions')->name('prefecture.demandes.inscriptions.reject');
    // Privilèges de supervision : statut disciplinaire, validation, approbation, périodes.
    Route::patch('/prefecture/eleves/{eleve}/statut', [PrefectureDashboardController::class, 'modifierStatutEleve'])->middleware('role:prefetture')->name('prefecture.eleves.statut');
    Route::post('/prefecture/evaluations/{evaluation}/valider', [PrefectureDashboardController::class, 'validerEvaluation'])->middleware('role:prefetture')->name('prefecture.evaluations.valider');
    Route::get('/prefecture/periodes', [PrefectureDashboardController::class, 'periodes'])->middleware('role:prefetture')->name('prefecture.periodes');
    Route::patch('/prefecture/periodes/{periode}/statut', [PrefectureDashboardController::class, 'togglePeriode'])->middleware('role:prefetture')->name('prefecture.periodes.toggle');
    Route::get('/prefecture/bulletins', [PrefectureDashboardController::class, 'bulletins'])->middleware('role:prefetture', 'permission:bulletins_validation')->name('prefecture.bulletins');
    Route::post('/prefecture/bulletins/{bulletin}/approuver', [PrefectureDashboardController::class, 'approuverBulletin'])->middleware('role:prefetture', 'permission:bulletins_validation')->name('prefecture.bulletins.approuver');
    Route::get('/prefecture/bulletins/{inscription}/document', [\App\Http\Controllers\BulletinController::class, 'document'])->middleware('role:prefetture', 'permission:bulletins_validation')->name('prefecture.bulletins.document');
    // Contrôle exécutif du Préfet : décisions/injonctions et flux académique.
    Route::get('/prefecture/decisions', [JournalDecisionController::class, 'index'])->middleware('role:prefetture', 'permission:decisions')->name('prefecture.decisions.index');
    Route::post('/prefecture/decisions', [JournalDecisionController::class, 'store'])->middleware('role:prefetture', 'permission:decisions')->name('prefecture.decisions.store');
    Route::get('/prefecture/flux-academique', [FluxAcademiqueController::class, 'index'])->middleware('role:prefetture', 'permission:flux_academique')->name('prefecture.flux.index');
    Route::put('/prefecture/flux-academique', [FluxAcademiqueController::class, 'update'])->middleware('role:prefetture', 'permission:flux_academique')->name('prefecture.flux.update');
    Route::get('/titulaire/tableau-de-bord', [TitulaireDashboardController::class, 'index'])->middleware('role:titulaire')->name('titulaire.dashboard');
    Route::get('/titulaire/bulletins', [TitulaireBulletinController::class, 'index'])->middleware('role:titulaire')->name('titulaire.bulletins.index');
    Route::post('/titulaire/bulletins/generer', [TitulaireBulletinController::class, 'generate'])->middleware('role:titulaire')->name('titulaire.bulletins.generate');
    Route::post('/titulaire/bulletins/appreciations', [TitulaireBulletinController::class, 'appreciations'])->middleware('role:titulaire')->name('titulaire.bulletins.appreciations');
    Route::get('/titulaire/bulletins/{inscription}/document', [TitulaireBulletinController::class, 'document'])->middleware('role:titulaire')->name('titulaire.bulletins.document');
    Route::get('/professeur/tableau-de-bord', [ProfesseurDashboardController::class, 'index'])->middleware('role:professeur')->name('professeur.dashboard');
    Route::get('/comptable/tableau-de-bord', [ComptableDashboardController::class, 'index'])->middleware('role:comptable')->name('comptable.dashboard');
    Route::get('/comptable/profil', [ComptableProfilController::class, 'index'])->middleware('role:comptable')->name('comptable.profil');
    Route::put('/comptable/profil', [ComptableProfilController::class, 'update'])->middleware('role:comptable')->name('comptable.profil.update');
    Route::patch('/comptable/profil/mot-de-passe', [ComptableProfilController::class, 'updatePassword'])->middleware('role:comptable')->name('comptable.profil.password');
    // Espace Parent : page de communication dédiée, ouverte aux parents (et à
    // l'admin pour prévisualisation) quand le module est actif.
    Route::get('/espace-parent', [PortailParentController::class, 'index'])->middleware('role:parent,admin')->name('parent.dashboard');
    // Une page par rubrique : évite de tout charger sur une page unique.
    Route::get('/espace-parent/notifications', [PortailParentController::class, 'notifications'])->middleware('role:parent,admin')->name('parent.notifications');
    Route::get('/espace-parent/notifications/{notification}', [PortailParentController::class, 'notification'])->middleware('role:parent,admin')->name('parent.notification');
    Route::get('/espace-parent/messages', [PortailParentController::class, 'messages'])->middleware('role:parent,admin')->name('parent.messages');
    Route::get('/espace-parent/enfants', [PortailParentController::class, 'enfants'])->middleware('role:parent,admin')->name('parent.enfants');
    Route::get('/espace-parent/profil', [PortailParentController::class, 'profil'])->middleware('role:parent,admin')->name('parent.profil');
    // Espace enseignant : une page par fonction.
    Route::get('/professeur/planning', [ProfesseurEspaceController::class, 'planning'])->middleware('role:professeur')->name('professeur.planning');
    Route::get('/professeur/profil', [ProfesseurEspaceController::class, 'profil'])->middleware('role:professeur')->name('professeur.profil');
    Route::get('/professeur/cours', [ProfesseurEspaceController::class, 'cours'])->middleware('role:professeur')->name('professeur.cours');
    Route::get('/professeur/cours/{affectation}', [ProfesseurEspaceController::class, 'coursShow'])->middleware('role:professeur')->name('professeur.cours.show');
    Route::get('/professeur/eleves', [ProfesseurEspaceController::class, 'eleves'])->middleware('role:professeur')->name('professeur.eleves');
    Route::get('/professeur/eleves/{eleve}', [ProfesseurEspaceController::class, 'eleveShow'])->middleware('role:professeur')->name('professeur.eleves.show');
    Route::get('/professeur/evaluations', [ProfesseurEvaluationController::class, 'index'])->middleware('role:professeur')->name('professeur.evaluations');
    Route::post('/professeur/evaluations', [ProfesseurEvaluationController::class, 'store'])->middleware('role:professeur')->name('professeur.evaluations.store');
    Route::get('/professeur/evaluations/{evaluation}/notes', [ProfesseurEvaluationController::class, 'notes'])->middleware('role:professeur')->name('professeur.notes');
    Route::post('/professeur/evaluations/{evaluation}/notes', [ProfesseurEvaluationController::class, 'storeNotes'])->middleware('role:professeur')->name('professeur.notes.store');
    Route::get('/professeur/presences', [ProfesseurPresenceController::class, 'index'])->middleware('role:professeur')->name('professeur.presences');
    Route::post('/professeur/presences', [ProfesseurPresenceController::class, 'store'])->middleware('role:professeur')->name('professeur.presences.store');
    Route::get('/comptable/paiements', [ComptablePaiementController::class, 'index'])->middleware('role:comptable', 'permission:frais_recus')->name('comptable.paiements.index');
    Route::post('/comptable/paiements', [ComptablePaiementController::class, 'store'])->middleware('role:comptable', 'permission:frais_recus')->name('comptable.paiements.store');
    Route::get('/comptable/paiements/{paiement}/recu', [RecuController::class, 'show'])->middleware('role:comptable', 'permission:frais_recus')->name('comptable.paiements.recu');
    // Le même reçu, imprimable depuis le dossier élève de l'administration et du préfet.
    Route::get('/admin/paiements/{paiement}/recu', [RecuController::class, 'show'])->middleware('role:admin')->name('admin.paiements.recu');
    Route::get('/prefecture/paiements/{paiement}/recu', [RecuController::class, 'show'])->middleware('role:prefetture')->name('prefecture.paiements.recu');
    Route::get('/comptable/recouvrement', [ComptableRecouvrementController::class, 'index'])->middleware('role:comptable')->name('comptable.recouvrement.index');
    Route::get('/comptable/depenses', [ComptableDepenseController::class, 'index'])->middleware('role:comptable')->name('comptable.depenses.index');
    Route::post('/comptable/depenses', [ComptableDepenseController::class, 'store'])->middleware('role:comptable')->name('comptable.depenses.store');
    Route::post('/comptable/depenses/natures', [ComptableDepenseController::class, 'storeNature'])->middleware('role:comptable')->name('comptable.depenses.natures.store');
    Route::delete('/comptable/depenses/{depense}', [ComptableDepenseController::class, 'destroy'])->middleware('role:comptable')->name('comptable.depenses.destroy');
    Route::get('/comptable/paies', [PaieController::class, 'index'])->middleware('role:comptable')->name('comptable.paies.index');
    Route::post('/comptable/paies/salaires', [PaieController::class, 'storeSalaire'])->middleware('role:comptable')->name('comptable.paies.salaires.store');
    Route::post('/comptable/paies', [PaieController::class, 'storePaie'])->middleware('role:comptable')->name('comptable.paies.store');
});
