<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Eleve;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Cycle;
use App\Models\Inscription;
use App\Models\Tuteur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@babazin.test'],
            [
                'username' => 'admin',
                'password' => 'password',
                'pin_code' => Hash::make('1234'),
                'role' => 'admin',
                'statut' => 'actif',
            ],
        );

        $annee = AnneeScolaire::updateOrCreate(['libelle' => '2026-2027'], ['date_debut' => '2026-09-01', 'date_fin' => '2027-07-31', 'is_encours' => true, 'statut' => 'actif']);
        $cycle = Cycle::updateOrCreate(['nom' => 'Primaire'], ['statut' => 'actif']);
        $classeDeux = Classe::updateOrCreate(['nom' => '2ème primaire'], ['cycle_id' => $cycle->id, 'statut' => 'actif']);
        $classeTrois = Classe::updateOrCreate(['nom' => '3ème primaire'], ['cycle_id' => $cycle->id, 'statut' => 'actif']);
        $tuteurDaniel = Tuteur::updateOrCreate(['telephone_mere' => '0810000001'], ['nom_mere' => 'Banzolo Marie', 'statut' => 'actif']);
        $tuteurAnnie = Tuteur::updateOrCreate(['telephone_mere' => '0810000002'], ['nom_mere' => 'Bokonde Chantal', 'statut' => 'actif']);
        $daniel = Eleve::updateOrCreate(['matricule' => 'ELV-2026-001'], ['nom' => 'Banzolo', 'postnom' => 'Bofotola', 'prenom' => 'Daniel', 'sexe' => 'M', 'date_naissance' => '2017-05-12', 'tuteur_id' => $tuteurDaniel->id, 'statut' => 'actif']);
        $annie = Eleve::updateOrCreate(['matricule' => 'ELV-2026-002'], ['nom' => 'Bokonde', 'postnom' => 'Nsombola', 'prenom' => 'Annie', 'sexe' => 'F', 'date_naissance' => '2016-08-23', 'tuteur_id' => $tuteurAnnie->id, 'statut' => 'actif']);
        Inscription::updateOrCreate(['eleve_id' => $daniel->id, 'annee_scolaire_id' => $annee->id], ['classe_id' => $classeDeux->id, 'date_inscription' => '2026-09-01', 'statut' => 'actif']);
        Inscription::updateOrCreate(['eleve_id' => $annie->id, 'annee_scolaire_id' => $annee->id], ['classe_id' => $classeTrois->id, 'date_inscription' => '2026-09-01', 'statut' => 'actif']);
    }
}
