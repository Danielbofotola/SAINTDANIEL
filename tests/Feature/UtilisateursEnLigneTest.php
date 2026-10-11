<?php

use App\Models\User;
use App\Support\PresenceUtilisateurs;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['sessions', 'professeurs', 'parametres_systeme', 'users'] as $table) {
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

    // Table des sessions du pilote « database » de Laravel.
    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
});

function sessionActive(string $id, ?int $userId, string $ip, string $agent, int $minutesAgo = 0): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => $userId,
        'ip_address' => $ip,
        'user_agent' => $agent,
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->subMinutes($minutesAgo)->getTimestamp(),
    ]);
}

test('the admin sees the accounts currently online, not the stale sessions', function () {
    $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin.test']);
    $comptable = User::factory()->create(['role' => 'comptable', 'username' => 'comptable.test']);

    sessionActive('sess-admin', $admin->id, '10.0.0.1', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0', 0);
    sessionActive('sess-vieux', $comptable->id, '10.0.0.2', 'Mozilla/5.0 Firefox/121.0', 40);
    sessionActive('sess-invite', null, '10.0.0.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari', 0);

    $this->actingAs($admin)
        ->get(route('admin.profile.online'))
        ->assertOk()
        ->assertSee('Utilisateurs en ligne')
        ->assertSee('admin.test')
        ->assertSee('10.0.0.1')
        ->assertSee('Chrome · Windows')
        ->assertDontSee('comptable.test')          // session trop ancienne
        ->assertDontSee('10.0.0.2')
        ->assertSee('Sessions invitées')           // session sans compte
        ->assertSee('Safari · iPhone');

    expect(PresenceUtilisateurs::idsEnLigne())->toBe([$admin->id]);

    // Le profil administrateur donne accès à la page.
    $this->actingAs($admin)
        ->get(route('admin.profile.show'))
        ->assertOk()
        ->assertSee(route('admin.profile.online'), false);
});

test('online users page is reserved to the admin role', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)->get(route('admin.profile.online'))->assertForbidden();
});

test('the device label is derived from the user agent', function () {
    expect(PresenceUtilisateurs::appareil('Mozilla/5.0 (Windows NT 10.0) Chrome/120.0'))->toBe('Chrome · Windows')
        ->and(PresenceUtilisateurs::appareil('Mozilla/5.0 (Linux; Android 13) Firefox/121.0'))->toBe('Firefox · Android')
        ->and(PresenceUtilisateurs::appareil(null))->toBe('Appareil inconnu')
        ->and(PresenceUtilisateurs::appareil(''))->toBe('Appareil inconnu');
});
