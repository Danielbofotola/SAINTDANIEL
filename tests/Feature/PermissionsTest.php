<?php

use App\Http\Middleware\EnsureUserHasPermission;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

test('la matrice des permissions traduit les niveaux de la table de référence', function () {
    expect(Permissions::niveau('systeme', 'modules'))->toBe('total')
        ->and(Permissions::niveau('prefetture', 'bulletins_validation'))->toBe('avis')
        ->and(Permissions::niveau('systeme', 'emplois_du_temps'))->toBe('lecture')
        ->and(Permissions::niveau('professeur', 'saisie_notes'))->toBe('oui')
        ->and(Permissions::niveau('directeur_etudes', 'inscriptions'))->toBe('oui')
        ->and(Permissions::niveau('comptable', 'frais_recus'))->toBe('oui')
        // Tout niveau autre que « non » autorise ; « non » (ou poste absent) refuse.
        ->and(Permissions::autorise('systeme', 'modules'))->toBeTrue()
        ->and(Permissions::autorise('secretaire', 'modules'))->toBeFalse()
        ->and(Permissions::autorise('prefetture', 'bulletins_validation'))->toBeTrue()
        ->and(Permissions::autorise('professeur', 'saisie_notes'))->toBeTrue()
        ->and(Permissions::autorise('professeur', 'modules'))->toBeFalse()
        ->and(Permissions::autorise('comptable', 'frais_recus'))->toBeTrue()
        ->and(Permissions::autorise('comptable', 'saisie_notes'))->toBeFalse()
        // Le super-administrateur passe partout ; un poste inconnu, jamais.
        ->and(Permissions::autorise('admin', 'audit'))->toBeTrue()
        ->and(Permissions::autorise(null, 'audit'))->toBeFalse();
});

test('le middleware « permission » bloque un poste sans droit et laisse passer les autres', function () {
    $middleware = new EnsureUserHasPermission();
    $requete = Request::create('/test');
    $requete->setUserResolver(fn () => new User(['role' => 'secretaire']));
    $suivant = fn () => response('ok');

    expect(fn () => $middleware->handle($requete, $suivant, 'modules'))
        ->toThrow(HttpException::class);

    expect($middleware->handle($requete, $suivant, 'frais_recus')->getContent())->toBe('ok');
});

test('la page « Matrice des permissions » est réservée à l’administrateur', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertSee('Matrice des permissions')
        ->assertSee('Commuter les modules');

    $this->actingAs(User::factory()->create(['role' => 'professeur']))
        ->get(route('admin.permissions.index'))
        ->assertForbidden();
});

test('le poste de travail liste les droits du rôle', function () {
    $this->actingAs(User::factory()->create(['role' => 'secretaire']))
        ->get(route('poste.show'))
        ->assertOk()
        ->assertSee('Poste de travail')
        ->assertSee('Perception des Frais Scolaires')
        ->assertSee('Saisie des Notes');

    // Réservé aux postes pilotés par la matrice.
    $this->actingAs(User::factory()->create(['role' => 'professeur']))
        ->get(route('poste.show'))
        ->assertForbidden();
});

test('les fonctionnalités exécutives et le poste Disciplinaire sont dans la matrice', function () {
    expect(in_array('disciplinaire', Permissions::roles(), true))->toBeTrue()
        ->and(Permissions::autorise('prefetture', 'decisions'))->toBeTrue()
        ->and(Permissions::niveau('prefetture', 'decisions'))->toBe('oui')
        ->and(Permissions::autorise('prefetture', 'flux_academique'))->toBeTrue()
        ->and(Permissions::autorise('disciplinaire', 'discipline'))->toBeTrue()
        ->and(Permissions::niveau('disciplinaire', 'discipline'))->toBe('oui')
        ->and(Permissions::autorise('disciplinaire', 'modules'))->toBeFalse()
        ->and(Permissions::autorise('secretaire', 'discipline'))->toBeFalse();
});
