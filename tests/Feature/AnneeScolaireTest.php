<?php

use App\Models\AnneeScolaire;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('inscriptions');
    Schema::dropIfExists('annees_scolaires');
    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle', 20)->unique();
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });

    Schema::dropIfExists('users');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('professeur_id')->nullable();
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

test('an admin can view the school years page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    AnneeScolaire::create([
        'libelle' => '2024-2025',
        'date_debut' => '2024-09-01',
        'date_fin' => '2025-07-15',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.annees.index'))
        ->assertOk()
        ->assertSee('Années scolaires')
        ->assertSee('2024-2025');
});

test('an admin can create a school year', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.annees.store'), [
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-07-15',
        ])
        ->assertRedirect(route('admin.annees.index'));

    $this->assertDatabaseHas('annees_scolaires', [
        'libelle' => '2025-2026',
        'statut' => 'actif',
        'is_encours' => false,
    ]);
});

test('creating a school year rejects an end date before the start date', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.annees.store'), [
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2025-08-01',
        ])
        ->assertSessionHasErrors('date_fin');
});

test('activating a school year deactivates all the others', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $previous = AnneeScolaire::create([
        'libelle' => '2024-2025',
        'date_debut' => '2024-09-01',
        'date_fin' => '2025-07-15',
        'is_encours' => true,
    ]);
    $current = AnneeScolaire::create([
        'libelle' => '2025-2026',
        'date_debut' => '2025-09-01',
        'date_fin' => '2026-07-15',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.annees.toggle', $current))
        ->assertRedirect();

    expect($current->fresh()->is_encours)->toBeTrue();
    expect($previous->fresh()->is_encours)->toBeFalse();
});

test('deactivating the current school year turns it off', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create([
        'libelle' => '2025-2026',
        'date_debut' => '2025-09-01',
        'date_fin' => '2026-07-15',
        'is_encours' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.annees.toggle', $annee))
        ->assertRedirect();

    expect($annee->fresh()->is_encours)->toBeFalse();
});

test('the current school year is exposed through the enCours helper', function () {
    AnneeScolaire::create(['libelle' => '2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-07-15']);
    $current = AnneeScolaire::create(['libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-15', 'is_encours' => true]);

    expect(AnneeScolaire::enCours()?->id)->toBe($current->id);
});

test('an admin can open the edit form of a school year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-15']);

    $this->actingAs($admin)
        ->get(route('admin.annees.edit', $annee))
        ->assertOk()
        ->assertSee('Modifier une année scolaire');
});

test('an admin can update a school year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-15']);

    $this->actingAs($admin)
        ->put(route('admin.annees.update', $annee), [
            'libelle' => '2025-2026 bis',
            'date_debut' => '2025-10-01',
            'date_fin' => '2026-08-01',
        ])
        ->assertRedirect(route('admin.annees.index'));

    $this->assertDatabaseHas('annees_scolaires', [
        'id' => $annee->id,
        'libelle' => '2025-2026 bis',
    ]);
});

test('an admin can delete a school year that is not used', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-15']);

    $this->actingAs($admin)
        ->delete(route('admin.annees.destroy', $annee))
        ->assertRedirect(route('admin.annees.index'));

    $this->assertDatabaseMissing('annees_scolaires', ['id' => $annee->id]);
});

test('deleting a school year referenced by an inscription is refused', function () {
    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('annee_scolaire_id');
    });

    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create(['libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-15']);
    DB::table('inscriptions')->insert(['annee_scolaire_id' => $annee->id]);

    $this->actingAs($admin)
        ->delete(route('admin.annees.destroy', $annee))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('annees_scolaires', ['id' => $annee->id]);
});

test('toggling a school year off then on restores it as the current year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create([
        'libelle' => '2025-2026',
        'date_debut' => '2025-09-01',
        'date_fin' => '2026-07-15',
        'is_encours' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.annees.toggle', $annee))->assertRedirect();
    expect($annee->fresh()->is_encours)->toBeFalse();

    $this->patch(route('admin.annees.toggle', $annee))->assertRedirect();
    expect($annee->fresh()->is_encours)->toBeTrue();
});
