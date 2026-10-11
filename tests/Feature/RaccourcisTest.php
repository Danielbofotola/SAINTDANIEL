<?php

use App\Models\User;
use App\Support\Raccourcis;
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
});

test('the admin shortcuts point to the real modules of the template', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $raccourcis = Raccourcis::pour($admin);

    expect($raccourcis['direct']['libelle'])->toBe('Documents')
        ->and($raccourcis['direct']['url'])->toBe(route('admin.eleves.index'));

    $urls = array_map(fn (array $rapport) => $rapport[2], $raccourcis['rapports']);

    expect($urls)->toContain(route('admin.statistiques.index'))    // Rapports journaliers
        ->toContain(route('admin.comptabilite.index'))             // Comptabilité OHADA
        ->toContain(route('admin.evaluations.index'))              // Évaluations & examens
        ->toContain(route('admin.paies.index'));                   // Paie & salaires

    // Le rendu de la barre contient le lien direct, le menu « Rapports » et les accès communs.
    $html = view('partials.raccourcis', ['raccourcis' => $raccourcis])->render();

    expect($html)->toContain('raccourcis-nav')
        ->toContain('>Rapports<')
        ->toContain(route('admin.paies.index'))
        ->toContain(route('messagerie.index'))
        ->toContain(route('notifications.index'));
});

test('the teacher shortcuts point to his own pages', function () {
    $user = User::factory()->create(['role' => 'professeur']);
    $raccourcis = Raccourcis::pour($user);

    expect($raccourcis['direct']['url'])->toBe(route('professeur.eleves'));

    $html = view('partials.raccourcis', ['raccourcis' => $raccourcis])->render();

    expect($html)->toContain(route('professeur.planning'))
        ->toContain(route('professeur.cours'))
        ->toContain(route('professeur.evaluations'))
        ->toContain(route('professeur.presences'))
        ->toContain(route('notifications.index'));
});

test('the other roles get their own shortcuts without admin pages', function () {
    $comptable = Raccourcis::pour(User::factory()->create(['role' => 'comptable']));
    $titulaire = Raccourcis::pour(User::factory()->create(['role' => 'titulaire']));
    $prefet = Raccourcis::pour(User::factory()->create(['role' => 'prefetture']));

    expect($comptable['direct']['url'])->toBe(route('comptable.paiements.index'))
        ->and(array_map(fn (array $r) => $r[2], $comptable['rapports']))
            ->toContain(route('comptable.depenses.index'))
            ->toContain(route('comptable.paies.index'))
        ->and($titulaire['direct']['url'])->toBe(route('titulaire.dashboard'));

    // La préfecture n'a que son espace : aucun lien d'administration.
    expect($prefet['direct']['url'])->toBe(route('prefecture.dashboard'))
        ->and($prefet['rapports'])->toBe([]);

    // Aucun lien d'administration ne fuite vers les rôles non-admin.
    $tous = array_map(fn (array $r) => $r[2], array_merge($comptable['rapports'], $titulaire['rapports']));

    expect($tous)->not->toContain(route('admin.statistiques.index'))
        ->not->toContain(route('admin.paies.index'));

    // Un invité (aucun utilisateur connecté) n'obtient que l'entrée de repli.
    expect(Raccourcis::pour(null)['rapports'])->toBe([]);
});
