<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('users');
    Schema::dropIfExists('professeurs');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
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

    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('statut')->default('actif');
    });
});

function accountProfilePayload(User $user, array $overrides = []): array
{
    return array_merge([
        'nom' => $user->nom,
        'postnom' => $user->postnom,
        'prenom' => $user->prenom,
        'username' => $user->username,
        'email' => $user->email,
        'role' => 'comptable',
        'statut' => 'actif',
        'professeur_id' => null,
    ], $overrides);
}

test('the dedicated page shows the account and the credentials form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->get(route('admin.profile.accounts.password.edit', $user))
        ->assertOk()
        ->assertSee('Réinitialiser les identifiants')
        ->assertSee($user->username)
        ->assertSee('Nouveau code PIN')
        ->assertSee(route('admin.profile.accounts.password.update', $user));
});

test('an admin can reset another account password from the dedicated page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), [
            'password' => 'nouveau-secret',
            'password_confirmation' => 'nouveau-secret',
        ])
        ->assertRedirect(route('admin.profile.accounts'));

    expect(Hash::check('nouveau-secret', $user->fresh()->password))->toBeTrue();
});

test('an admin can set and then remove the login PIN', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable', 'pin_code' => null]);

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), [
            'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1', 'pin_code' => '4321',
        ])
        ->assertRedirect();

    expect(Hash::check('4321', $user->fresh()->pin_code))->toBeTrue();

    $this->patch(route('admin.profile.accounts.password.update', $user), [
        'password' => 'motdepasse2', 'password_confirmation' => 'motdepasse2', 'remove_pin' => '1',
    ])
        ->assertRedirect();

    expect($user->fresh()->pin_code)->toBeNull();
});

test('omitting the PIN keeps the current one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $pinBefore = $user->pin_code;

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), [
            'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1',
        ])
        ->assertRedirect();

    expect($user->fresh()->pin_code)->toBe($pinBefore);
});

test('a weak password or a wrong confirmation is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $passwordBefore = $user->password;

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), [
            'password' => 'court', 'password_confirmation' => 'court',
        ])
        ->assertSessionHasErrors('password');

    $this->patch(route('admin.profile.accounts.password.update', $user), [
        'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse2',
    ])
        ->assertSessionHasErrors('password');

    expect($user->fresh()->password)->toBe($passwordBefore);
});

test('a password is required to reset an account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $passwordBefore = $user->password;

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), ['pin_code' => '1234'])
        ->assertSessionHasErrors('password');

    expect($user->fresh()->password)->toBe($passwordBefore);
});

test('a PIN that is not four digits is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $pinBefore = $user->pin_code;

    $this->actingAs($admin)
        ->patch(route('admin.profile.accounts.password.update', $user), [
            'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1', 'pin_code' => '12',
        ])
        ->assertSessionHasErrors('pin_code');

    expect($user->fresh()->pin_code)->toBe($pinBefore);
});

test('an admin cannot reset their own password from this page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.accounts.password.edit', $admin))
        ->assertRedirect(route('admin.profile.edit'));

    $this->patch(route('admin.profile.accounts.password.update', $admin), [
        'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1',
    ])->assertRedirect(route('admin.profile.edit'));
});

test('the profile update no longer touches the password or the PIN', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $passwordBefore = $user->password;
    $pinBefore = $user->pin_code;

    $this->actingAs($admin)
        ->put(route('admin.profile.accounts.update', $user), accountProfilePayload($user, ['nom' => 'Nouveau']))
        ->assertRedirect(route('admin.profile.accounts'));

    $user->refresh();

    expect($user->password)->toBe($passwordBefore)
        ->and($user->pin_code)->toBe($pinBefore)
        ->and($user->nom)->toBe('Nouveau');
});

test('the account edit page links to the dedicated credentials page', function () {
    DB::table('professeurs')->insert([
        'id' => 1, 'matricule' => 'PROF-2026-0001', 'nom' => 'Kabila', 'postnom' => 'Mwamba',
        'prenom' => 'Aline', 'statut' => 'inactif',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'professeur', 'professeur_id' => 1]);

    $this->actingAs($admin)
        ->get(route('admin.profile.accounts.edit', $user))
        ->assertOk()
        ->assertSee('PROF-2026-0001')
        ->assertSee(route('admin.profile.accounts.update', $user))
        ->assertSee(route('admin.profile.accounts.password.edit', $user))
        ->assertDontSee('name="password"', false);
});
