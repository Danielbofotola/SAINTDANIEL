<?php

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
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

test('the admin launches a notification to every user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin.test']);
    $prof = User::factory()->create(['role' => 'professeur', 'username' => 'prof.test']);
    $comptable = User::factory()->create(['role' => 'comptable', 'username' => 'comptable.test']);

    $this->actingAs($admin)
        ->get(route('admin.annonces.index'))
        ->assertOk()
        ->assertSee('Lancer une notification');

    $this->actingAs($admin)
        ->post(route('admin.annonces.store'), [
            'titre' => 'Réunion pédagogique vendredi',
            'message' => 'Tous les enseignants sont attendus à 14 h en salle polyvalente.',
            'niveau' => 'warning',
            'cible' => 'tous',
            'lien' => '/professeur/planning',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '3 utilisateur(s)'));

    expect(DB::table('annonces')->count())->toBe(1)
        ->and(DB::table('notifications')->count())->toBe(3);

    foreach ([$admin, $prof, $comptable] as $compte) {
        expect($compte->unreadNotifications()->count())->toBe(1)
            ->and($compte->notifications()->first()->data['titre'])->toBe('Réunion pédagogique vendredi');
    }
});

test('a targeted announcement only reaches the chosen role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $prof = User::factory()->create(['role' => 'professeur']);
    $autre = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->post(route('admin.annonces.store'), [
            'titre' => 'Cahiers de textes à déposer',
            'message' => 'Merci de déposer vos cahiers avant vendredi.',
            'niveau' => 'info',
            'cible' => 'professeur',
        ])
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '1 utilisateur(s)'));

    expect($prof->unreadNotifications()->count())->toBe(1)
        ->and($autre->unreadNotifications()->count())->toBe(0)
        ->and($admin->unreadNotifications()->count())->toBe(0);
});

test('a user reads his notifications and marks them as read', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $prof = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($admin)->post(route('admin.annonces.store'), [
        'titre' => 'Fermeture exceptionnelle',
        'message' => 'L’établissement sera fermé lundi.',
        'niveau' => 'danger',
        'cible' => 'tous',
    ]);

    $notification = $prof->notifications()->first();

    $this->actingAs($prof)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Mes notifications')
        ->assertSee('Fermeture exceptionnelle')
        ->assertSee('Nouveau');

    $this->actingAs($prof)
        ->patch(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($prof->unreadNotifications()->count())->toBe(0)
        ->and($prof->notifications()->first()->read_at)->not->toBeNull();

    // Une seconde annonce, puis « tout marquer comme lu ».
    $this->actingAs($admin)->post(route('admin.annonces.store'), [
        'titre' => 'Rappel', 'message' => 'Pensez aux bulletins.', 'niveau' => 'info', 'cible' => 'tous',
    ]);

    expect($prof->unreadNotifications()->count())->toBe(1);

    $this->actingAs($prof)
        ->patch(route('notifications.readall'))
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '1 notification'));

    expect($prof->unreadNotifications()->count())->toBe(0);
});

test('only the admin can launch an announcement, and deleting it clears the bell', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $prof = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($prof)
        ->post(route('admin.annonces.store'), [
            'titre' => 'Tentative', 'message' => 'Texte', 'niveau' => 'info', 'cible' => 'tous',
        ])
        ->assertForbidden();

    $this->actingAs($prof)->get(route('admin.annonces.index'))->assertForbidden();

    $this->actingAs($admin)->post(route('admin.annonces.store'), [
        'titre' => 'À supprimer', 'message' => 'Texte', 'niveau' => 'info', 'cible' => 'tous',
    ]);

    $annonce = Annonce::first();
    expect(DB::table('notifications')->count())->toBe(2);

    $this->actingAs($admin)
        ->delete(route('admin.annonces.destroy', $annonce))
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '2 notification(s) retirée(s)'));

    expect(DB::table('annonces')->count())->toBe(0)
        ->and(DB::table('notifications')->count())->toBe(0)
        ->and($prof->fresh()->unreadNotifications()->count())->toBe(0);
});
