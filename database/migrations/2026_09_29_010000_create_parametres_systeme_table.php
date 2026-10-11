<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres_systeme', function (Blueprint $table) {
            $table->id();
            $table->string('nom_etablissement', 150)->default('COMPLEXE SCOLAIRE BABAZIN');
            $table->string('nom_court', 60)->default('C.S. Moty');
            $table->string('telephone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('adresse')->nullable();
            $table->timestamps();
        });

        DB::table('parametres_systeme')->insert([
            'nom_etablissement' => 'COMPLEXE SCOLAIRE BABAZIN',
            'nom_court' => 'C.S. Moty',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_systeme');
    }
};
