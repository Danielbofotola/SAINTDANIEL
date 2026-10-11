<?php

use App\Models\ParametreSysteme;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('parametres_systeme');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150);
        $table->string('nom_court', 60);
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->boolean('saisie_notes_active')->default(true);
        $table->boolean('portail_parents_actif')->default(false);
        $table->boolean('deliberation_active')->default(false);
        $table->decimal('deliberation_seuil_admission', 5, 2)->default(50);
        $table->decimal('deliberation_seuil_repechage', 5, 2)->default(45);
        $table->unsignedInteger('deliberation_max_cours_echoues')->nullable();
        $table->decimal('deliberation_note_min_cours', 5, 2)->nullable();
        $table->string('bulletin_pays', 100)->nullable();
        $table->string('bulletin_ministere', 160)->nullable();
        $table->string('bulletin_titre', 160)->nullable();
        $table->string('bulletin_reference', 50)->nullable();
        $table->string('bulletin_province', 100)->nullable();
        $table->string('bulletin_ville', 100)->nullable();
        $table->string('bulletin_commune', 100)->nullable();
        $table->string('bulletin_code_ecole', 50)->nullable();
        $table->string('bulletin_lieu_signature', 100)->nullable();
        $table->string('bulletin_signataire', 120)->nullable();
        $table->decimal('bulletin_seuil_reussite', 5, 2)->default(50);
        $table->boolean('bulletin_afficher_place')->default(true);
        $table->boolean('bulletin_afficher_conduite')->default(true);
        $table->timestamps();
    });

    ParametreSysteme::create([
        'nom_etablissement' => 'COMPLEXE SCOLAIRE BABAZIN',
        'nom_court' => 'C.S. Moty',
    ]);
});

test('admin can update persistent school identity and contact settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.settings'))
        ->assertOk()
        ->assertSee('COMPLEXE SCOLAIRE BABAZIN')
        ->assertSee('C.S. Moty');

    $this->put(route('admin.profile.settings.update'), [
        'nom_etablissement' => 'INSTITUT MWANGAZA',
        'nom_court' => 'I.M. MWANGAZA',
        'telephone' => '+243 900 123 456',
        'email' => 'contact@mwangaza.example',
        'adresse' => 'Avenue de la Paix, Kinshasa',
    ])->assertRedirect(route('admin.profile.settings'));

    $this->get(route('admin.profile.settings'))
        ->assertOk()
        ->assertSee('INSTITUT MWANGAZA')
        ->assertSee('I.M. MWANGAZA')
        ->assertSee('+243 900 123 456')
        ->assertSee('contact@mwangaza.example')
        ->assertSee('Avenue de la Paix, Kinshasa');

    $this->view('projet.page-connexion.page-login-password')
        ->assertSee('INSTITUT MWANGAZA')
        ->assertSee('I.M. MWANGAZA')
        ->assertSee('contact@mwangaza.example');
});

test('non-admin users cannot change establishment settings', function () {
    $professor = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($professor)
        ->get(route('admin.profile.settings'))
        ->assertForbidden();
});
