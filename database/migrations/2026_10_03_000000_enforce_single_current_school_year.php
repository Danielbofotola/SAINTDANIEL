<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Garantit au niveau de la base qu'au plus une année scolaire est « en cours ».
 *
 * Le contrôleur gère déjà l'unicité applicative (transaction), mais rien
 * n'empêchait une écriture directe, un import ou une course de laisser deux
 * années avec is_encours = true.
 *
 * On ajoute une colonne générée qui vaut 1 pour l'année en cours et NULL
 * sinon ; un index UNIQUE tolère plusieurs NULL, donc au plus une ligne à 1.
 * La colonne étant générée par MySQL, elle reste cohérente sans code applicatif.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->supportsGeneratedColumns()) {
            return;
        }

        DB::statement(
            'ALTER TABLE annees_scolaires ADD COLUMN en_cours_unique TINYINT '
            .'GENERATED ALWAYS AS (IF(is_encours = 1, 1, NULL)) STORED'
        );

        DB::statement(
            'ALTER TABLE annees_scolaires ADD UNIQUE INDEX annees_scolaires_en_cours_unique (en_cours_unique)'
        );
    }

    public function down(): void
    {
        if (! $this->supportsGeneratedColumns()) {
            return;
        }

        DB::statement('ALTER TABLE annees_scolaires DROP INDEX annees_scolaires_en_cours_unique');
        DB::statement('ALTER TABLE annees_scolaires DROP COLUMN en_cours_unique');
    }

    private function supportsGeneratedColumns(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
