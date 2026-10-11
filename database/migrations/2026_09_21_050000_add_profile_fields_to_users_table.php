<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom', 50)->nullable()->after('professeur_id');
            $table->string('postnom', 50)->nullable()->after('nom');
            $table->string('prenom', 50)->nullable()->after('postnom');
            $table->date('date_naissance')->nullable()->after('prenom');
            $table->string('telephone', 20)->nullable()->after('email');
            $table->text('adresse')->nullable()->after('telephone');
            $table->string('photo')->nullable()->after('adresse');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'postnom', 'prenom', 'date_naissance', 'telephone', 'adresse', 'photo']);
        });
    }
};