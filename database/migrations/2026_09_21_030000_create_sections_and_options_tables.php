<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('nom', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('nom', 100);
            $table->text('description')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->unique(['section_id', 'code']);
            $table->unique(['section_id', 'nom']);
        });

        $sections = [
            ['code' => 'SCIENTIFIQUE', 'nom' => 'Scientifique'],
            ['code' => 'PEDAGOGIQUE', 'nom' => 'Pédagogique'],
            ['code' => 'LITTERAIRE', 'nom' => 'Littéraire'],
            ['code' => 'COMMERCIALE', 'nom' => 'Commerciale et administrative'],
            ['code' => 'TECHNIQUE', 'nom' => 'Technique'],
        ];

        foreach ($sections as $section) {
            $section['statut'] = 'actif';
            $section['created_at'] = now();
            $section['updated_at'] = now();
            DB::table('sections')->insert($section);
        }

        $sectionIds = DB::table('sections')->pluck('id', 'code');
        $options = [
            ['section' => 'SCIENTIFIQUE', 'code' => 'MATH-PHYSIQUE', 'nom' => 'Mathématiques - Physique'],
            ['section' => 'SCIENTIFIQUE', 'code' => 'BIOCHIMIE', 'nom' => 'Biologie - Chimie'],
            ['section' => 'PEDAGOGIQUE', 'code' => 'PEDAGOGIE-GENERALE', 'nom' => 'Pédagogie générale'],
            ['section' => 'PEDAGOGIQUE', 'code' => 'EDUCATION-PHYSIQUE', 'nom' => 'Éducation physique'],
            ['section' => 'LITTERAIRE', 'code' => 'LATIN-PHILO', 'nom' => 'Latin - Philosophie'],
            ['section' => 'COMMERCIALE', 'code' => 'COMMERCIALE-ADMINISTRATIVE', 'nom' => 'Commerciale et administrative'],
            ['section' => 'TECHNIQUE', 'code' => 'ELECTRICITE', 'nom' => 'Électricité'],
            ['section' => 'TECHNIQUE', 'code' => 'MECANIQUE', 'nom' => 'Mécanique générale'],
        ];

        foreach ($options as $option) {
            DB::table('options')->insert([
                'section_id' => $sectionIds[$option['section']],
                'code' => $option['code'],
                'nom' => $option['nom'],
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['section_id', 'option_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['option_id']);
            $table->dropColumn(['section_id', 'option_id']);
            $table->string('section_id', 50)->nullable();
            $table->string('option_id', 50)->nullable();
        });

        Schema::dropIfExists('options');
        Schema::dropIfExists('sections');
    }
};