<?php

use App\Models\AffectationCours;
use App\Models\ArchiveAnnee;
use App\Models\Bulletin;
use App\Models\CategorieDepense;
use App\Models\Cours;
use App\Models\Depense;
use App\Models\DocumentProfesseur;
use App\Models\EmploiDuTemps;
use App\Models\Evaluation;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\Periode;
use App\Models\TypeFrais;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Le scope anneeEnCours() interroge l'année en cours : la table doit exister.
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
});

test('the new models map to the expected tables', function () {
    expect((new Periode)->getTable())->toBe('periodes')
        ->and((new Cours)->getTable())->toBe('cours')
        ->and((new AffectationCours)->getTable())->toBe('affectations_cours')
        ->and((new EmploiDuTemps)->getTable())->toBe('emplois_du_temps')
        ->and((new Evaluation)->getTable())->toBe('evaluations')
        ->and((new Note)->getTable())->toBe('notes')
        ->and((new Bulletin)->getTable())->toBe('bulletins')
        ->and((new TypeFrais)->getTable())->toBe('types_frais')
        ->and((new FraisScolaire)->getTable())->toBe('frais_scolaires')
        ->and((new Paiement)->getTable())->toBe('paiements')
        ->and((new CategorieDepense)->getTable())->toBe('categories_depenses')
        ->and((new Depense)->getTable())->toBe('depenses')
        ->and((new ArchiveAnnee)->getTable())->toBe('archives_annees')
        ->and((new DocumentProfesseur)->getTable())->toBe('documents_professeurs');
});

test('every model that carries a school year exposes the year relation and scope', function () {
    $models = [Inscription::class, Periode::class, AffectationCours::class, FraisScolaire::class, Depense::class, ArchiveAnnee::class];

    foreach ($models as $model) {
        expect((new $model)->anneeScolaire())->toBeInstanceOf(BelongsTo::class, "$model::anneeScolaire()");
        expect((new $model)->newQuery()->anneeEnCours())->toBeInstanceOf(Builder::class, "$model::anneeEnCours()");
    }
});

test('relation foreign keys match the database schema', function () {
    expect((new Depense)->categorie()->getForeignKeyName())->toBe('category_id')
        ->and((new AffectationCours)->professeur()->getForeignKeyName())->toBe('professeur_id')
        ->and((new AffectationCours)->emploisDuTemps()->getForeignKeyName())->toBe('affectation_id')
        ->and((new AffectationCours)->evaluations()->getForeignKeyName())->toBe('affectation_id')
        ->and((new EmploiDuTemps)->affectation()->getForeignKeyName())->toBe('affectation_id')
        ->and((new Evaluation)->affectation()->getForeignKeyName())->toBe('affectation_id')
        ->and((new TypeFrais)->fraisScolaires()->getForeignKeyName())->toBe('type_frais_id')
        ->and((new CategorieDepense)->depenses()->getForeignKeyName())->toBe('category_id')
        ->and((new DocumentProfesseur)->professeur()->getForeignKeyName())->toBe('professeur_id')
        ->and((new Inscription)->paiements()->getForeignKeyName())->toBe('inscription_id');
});
