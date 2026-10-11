<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnneeScolaire extends Model
{
    /**
     * Tables portant une clé étrangère vers annees_scolaires.
     */
    private const TABLES_LIEES = [
        'inscriptions',
        'periodes',
        'affectations_cours',
        'frais_scolaires',
        'depenses',
        'demandes_inscription',
    ];

    protected $table = 'annees_scolaires';
    protected $fillable = ['libelle', 'date_debut', 'date_fin', 'is_encours', 'statut'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'is_encours' => 'boolean',
        ];
    }

    /**
     * L'année scolaire « en cours » (unique), ou null si aucune n'est définie.
     */
    public static function enCours(): ?self
    {
        return static::where('is_encours', true)->first();
    }

    /**
     * L'année scolaire qui suit celle donnée (ou l'année en cours), d'après la
     * date de début. Elle matérialise le passage : la délibération de N aboutit
     * dans N+1. Renvoie null s'il n'existe aucune année postérieure.
     */
    public static function suivante(?self $annee = null): ?self
    {
        $annee ??= static::enCours();

        if ($annee === null) {
            return null;
        }

        return static::query()
            ->where('statut', 'actif')
            ->where('date_debut', '>', $annee->date_debut)
            ->orderBy('date_debut')
            ->first();
    }

    /**
     * L'année scolaire qui précède celle donnée (ou l'année en cours), d'après la
     * date de début. Sert à retrouver la délibération d'où vient un élève.
     */
    public static function precedente(?self $annee = null): ?self
    {
        $annee ??= static::enCours();

        if ($annee === null) {
            return null;
        }

        return static::query()
            ->where('statut', 'actif')
            ->where('date_debut', '<', $annee->date_debut)
            ->orderByDesc('date_debut')
            ->first();
    }

    /**
     * Les inscriptions rattachées à cette année scolaire.
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function periodes()
    {
        return $this->hasMany(Periode::class);
    }

    public function affectationsCours()
    {
        return $this->hasMany(AffectationCours::class);
    }

    public function fraisScolaires()
    {
        return $this->hasMany(FraisScolaire::class);
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    public function archive()
    {
        return $this->hasOne(ArchiveAnnee::class);
    }

    /**
     * Indique si l'année est référencée par des données métier
     * (inscriptions, périodes, finances…), ce qui interdit sa suppression.
     */
    public function isUtilisee(): bool
    {
        foreach (self::TABLES_LIEES as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('annee_scolaire_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}
