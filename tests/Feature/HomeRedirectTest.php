<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->date('date_naissance')->nullable();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
});

test('each role is sent from /accueil to its own dashboard', function () {
    $expected = [
        'admin' => 'admin.dashboard',
        'prefetture' => 'prefecture.dashboard',
        'titulaire' => 'titulaire.dashboard',
        'professeur' => 'professeur.dashboard',
        'comptable' => 'comptable.dashboard',
    ];

    foreach ($expected as $role => $routeName) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route($routeName));
    }
});

test('an unknown role is sent to the profile instead of looping on /accueil', function () {
    $user = User::factory()->create(['role' => 'role-inconnu']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('profile.show'));
});

test('every dashboard route name used by the role mapping exists', function () {
    foreach (['admin', 'prefetture', 'titulaire', 'professeur', 'comptable', null] as $role) {
        $routeName = User::dashboardRouteNameFor($role);

        expect(\Illuminate\Support\Facades\Route::has($routeName))->toBeTrue("route manquante : $routeName");
    }
});

test('the profile page links back to the role dashboard, not to the placeholder', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee(route('comptable.dashboard'))
        ->assertDontSee('/accueil');
});

test('an admin impersonating a user lands on that user dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->post(route('admin.profile.accounts.impersonate', $comptable))
        ->assertRedirect(route('comptable.dashboard'));

    $this->assertAuthenticatedAs($comptable);
});
