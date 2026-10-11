<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['annonces', 'notifications', 'professeurs', 'parametres_systeme', 'users'] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('photo')->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('Etablissement');
        $table->string('nom_court', 60)->default('Ecole');
        $table->timestamps();
    });
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole Test', 'created_at' => now(), 'updated_at' => now()]);

    Schema::create('annonces', function (Blueprint $table) {
        $table->id();
        $table->string('titre', 150);
        $table->text('message');
        $table->string('niveau', 20)->default('info');
        $table->string('lien', 255)->nullable();
        $table->string('cible', 30)->default('tous');
        $table->unsignedBigInteger('envoye_par')->nullable();
        $table->unsignedInteger('destinataires')->default(0);
        $table->timestamps();
    });
    Schema::create('notifications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->text('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
});

test('every role sees the floating bell with the unread count and the announcements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comptable = User::factory()->create(['role' => 'comptable', 'username' => 'comptable.test']);
    $prefet = User::factory()->create(['role' => 'prefetture', 'username' => 'prefet.test']);
    $titulaire = User::factory()->create(['role' => 'titulaire', 'username' => 'titulaire.test']);

    $this->actingAs($admin)->post(route('admin.annonces.store'), [
        'titre' => 'Réunion pédagogique vendredi',
        'message' => 'Présence obligatoire de tout le personnel à 14 h.',
        'niveau' => 'warning',
        'cible' => 'tous',
    ]);

    foreach ([$comptable, $prefet, $titulaire] as $compte) {
        Auth::login($compte);
        $cloche = view('partials.cloche-flottante')->render();

        expect($cloche)->toContain('cloche-flottante')
            ->toContain('aria-label="Notifications"')
            ->toContain('Réunion pédagogique vendredi')      // le titre de l'annonce
            ->toContain('data-non-lues')                     // le compteur de non lues
            ->toContain(route('notifications.index'));       // le lien vers la boîte de réception

        // La boîte de réception est accessible et affiche l'annonce.
        $this->actingAs($compte)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Réunion pédagogique vendredi')
            ->assertSee('Nouveau');
    }
});

test('the bell is hidden once everything is read', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)->post(route('admin.annonces.store'), [
        'titre' => 'Rappel', 'message' => 'Texte', 'niveau' => 'info', 'cible' => 'tous',
    ]);

    Auth::login($comptable);
    expect(view('partials.cloche-flottante')->render())->toContain('data-non-lues');

    $this->actingAs($comptable)->patch(route('notifications.readall'))->assertRedirect();

    $sansBadge = view('partials.cloche-flottante')->render();
    expect($sansBadge)->toContain('cloche-flottante')
        ->toContain('Rappel')                     // la notification reste consultable
        ->not->toContain('data-non-lues');
});
