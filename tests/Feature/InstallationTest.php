<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('users');
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
});

test('la page d’amorçage affiche le formulaire et crée le premier administrateur', function () {
    $this->get(route('systeme.installation'))
        ->assertOk()
        ->assertSee('Amorçage du système');

    $this->post(route('systeme.installation.store'), [
        'nom' => 'Bofotola', 'postnom' => 'Nzuzi', 'prenom' => 'Daniel',
        'email' => 'admin@ecole.test', 'username' => 'admin',
        'password' => 'secret-1234', 'password_confirmation' => 'secret-1234',
    ])->assertRedirect(route('login'));

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    expect($admin->role)->toBe('admin')->and($admin->statut)->toBe('actif');
});

test('la page d’amorçage se ferme dès qu’un administrateur existe', function () {
    $this->get(route('systeme.installation'))->assertOk();

    User::factory()->create(['role' => 'admin']);

    $this->get(route('systeme.installation'))->assertNotFound();
});

test('la page d’amorçage refuse un mot de passe non confirmé', function () {
    $this->from(route('systeme.installation'))
        ->post(route('systeme.installation.store'), [
            'nom' => 'A', 'prenom' => 'B',
            'email' => 'a@ecole.test', 'username' => 'a',
            'password' => 'secret-1234', 'password_confirmation' => 'autre',
        ])
        ->assertRedirect(route('systeme.installation'))
        ->assertSessionHasErrors('password');

    expect(User::query()->count())->toBe(0);
});

test('le jeton secret est exigé quand il est configuré', function () {
    config(['install.token' => 'sesame-2026']);

    $this->get(route('systeme.installation'))->assertNotFound();
    $this->get(route('systeme.installation', ['cle' => 'faux']))->assertNotFound();
    $this->get(route('systeme.installation', ['cle' => 'sesame-2026']))->assertOk();
});

test('la commande systeme:amorcage rouvre temporairement la page malgré un admin existant', function () {
    User::factory()->create(['role' => 'admin']);

    $this->artisan('systeme:amorcage', ['--minutes' => 5])->assertSuccessful();

    $cle = \Illuminate\Support\Facades\Cache::get('installation.cle');
    expect($cle)->toBeString()->not->toBe('');

    $this->get(route('systeme.installation'))->assertNotFound();
    $this->get(route('systeme.installation', ['cle' => $cle]))->assertOk();
});

test('la commande systeme:admin crée un administrateur puis réinitialise son mot de passe', function () {
    $this->artisan('systeme:admin', ['--username' => 'root', '--email' => 'root@ecole.test', '--password' => 'secret-1234'])
        ->expectsOutputToContain('créé')
        ->assertSuccessful();

    $admin = User::query()->where('username', 'root')->firstOrFail();
    expect($admin->role)->toBe('admin')
        ->and($admin->statut)->toBe('actif')
        ->and(\Illuminate\Support\Facades\Hash::check('secret-1234', $admin->password))->toBeTrue();

    $this->artisan('systeme:admin', ['--username' => 'root', '--password' => 'nouveau-1234'])
        ->expectsOutputToContain('réinitialisé')
        ->assertSuccessful();

    expect(\Illuminate\Support\Facades\Hash::check('nouveau-1234', $admin->fresh()->password))->toBeTrue()
        ->and(User::query()->where('role', 'admin')->count())->toBe(1);
});

test('la commande systeme:admin liste les administrateurs et refuse un mot de passe trop court', function () {
    $this->artisan('systeme:admin', ['--liste' => true])->expectsOutputToContain('Aucun administrateur')->assertSuccessful();

    $this->artisan('systeme:admin', ['--username' => 'x', '--email' => 'x@ecole.test', '--password' => 'court'])->assertFailed();
    expect(User::query()->count())->toBe(0);
});
