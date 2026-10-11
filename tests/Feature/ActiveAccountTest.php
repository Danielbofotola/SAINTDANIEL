<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['session.driver' => 'array']);

    Schema::disableForeignKeyConstraints();
    foreach (['sessions', 'users'] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('telephone', 20)->nullable();
        $table->string('photo')->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
});

test('an authenticated but inactive account is redirected to the login page', function () {
    $user = User::factory()->create(['role' => 'admin', 'statut' => 'inactif']);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('a user deactivated while connected loses the session immediately', function () {
    $user = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);

    $user->update(['statut' => 'inactif']);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('an inactive account cannot log in with its password', function () {
    $user = User::factory()->create([
        'role' => 'comptable',
        'statut' => 'inactif',
        'password' => bcrypt('motdepasse123'),
    ]);

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'motdepasse123'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('deactivating an account purges its stored sessions', function () {
    config(['session.driver' => 'database']);

    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
    $target = User::factory()->create(['role' => 'comptable', 'statut' => 'actif']);

    DB::table('sessions')->insert([
        'id' => 'session-du-comptable',
        'user_id' => $target->id,
        'payload' => base64_encode(serialize([])),
        'last_activity' => time(),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.toggle', $target))
        ->assertRedirect();

    expect(DB::table('users')->where('id', $target->id)->value('statut'))->toBe('inactif')
        ->and(DB::table('sessions')->where('user_id', $target->id)->count())->toBe(0);
});
