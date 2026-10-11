<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['options', 'sections', 'classes', 'cycles', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut')->default('actif');
    });

    // La table `sections` ne porte que le nom (colonnes code/description/statut retirées).
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('section_id');
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    DB::table('sections')->insert([
        ['id' => 1, 'nom' => 'Scientifique'],
        ['id' => 2, 'nom' => 'Littéraire'],
    ]);
    DB::table('options')->insert([
        ['id' => 1, 'section_id' => 1, 'nom' => 'Latin-Mathématiques'],
    ]);
    DB::table('classes')->insert([
        ['id' => 1, 'nom' => '1ère', 'section_id' => 1, 'option_id' => 1, 'statut' => 'actif'],
    ]);
});

test('the sections page lists the sections with their option and class counts', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertSee('Sections')
        ->assertSee('Scientifique')
        ->assertSee('Littéraire')
        ->assertSee('1 option(s)');
});

test('an admin can create a section', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.sections.store'), ['nom' => 'Technique', 'retour' => 'gestion'])
        ->assertRedirect(route('admin.sections.index'));

    $this->assertDatabaseHas('sections', ['nom' => 'Technique']);
});

test('a duplicate section name is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.sections.store'), ['nom' => 'Scientifique'])
        ->assertSessionHasErrors('nom');

    expect(DB::table('sections')->count())->toBe(2);
});

test('the options page lists the options with their section', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.options.index'))
        ->assertOk()
        ->assertSee('Latin-Mathématiques')
        ->assertSee('Scientifique')
        ->assertSee('Nouvelle option');
});

test('an admin can create an option attached to a section', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.options.store'), ['section_id' => 2, 'nom' => 'Grec-Latin', 'retour' => 'gestion'])
        ->assertRedirect(route('admin.options.index'));

    $this->assertDatabaseHas('options', ['nom' => 'Grec-Latin', 'section_id' => 2]);
});

test('an option name is unique inside its section but may exist elsewhere', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Même nom dans la même section : refusé
    $this->actingAs($admin)
        ->post(route('admin.options.store'), ['section_id' => 1, 'nom' => 'Latin-Mathématiques'])
        ->assertSessionHasErrors('nom');

    // Même nom dans une autre section : accepté
    $this->post(route('admin.options.store'), ['section_id' => 2, 'nom' => 'Latin-Mathématiques', 'retour' => 'gestion'])
        ->assertRedirect(route('admin.options.index'));

    expect(DB::table('options')->where('nom', 'Latin-Mathématiques')->where('section_id', 2)->exists())->toBeTrue();
});

test('creating an option requires a section', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.options.store'), ['nom' => 'Sans section'])
        ->assertSessionHasErrors('section_id');

    expect(DB::table('options')->count())->toBe(1);
});

test('the quick creation pages stay available and return to the class form', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.sections.create'))
        ->assertOk()
        ->assertSee('Créer une section');

    $this->get(route('admin.options.create', ['section_id' => 2]))
        ->assertOk()
        ->assertSee('Créer une option')
        ->assertSee('Littéraire');

    // Sans marqueur « gestion », la création ramène au formulaire de classe.
    $this->post(route('admin.sections.store'), ['nom' => 'Technique'])
        ->assertRedirect(route('admin.classes.create', ['section_id' => 3]));
});
