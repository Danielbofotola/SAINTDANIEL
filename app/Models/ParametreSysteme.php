<?php

namespace App\Models;

use App\Support\Monnaie;
use Illuminate\Database\Eloquent\Model;

class ParametreSysteme extends Model
{
    protected $table = 'parametres_systeme';

    /** Seuil de réussite appliqué au bulletin lorsque rien n'est configuré. */
    public const SEUIL_REUSSITE_DEFAUT = 50.0;

    protected $fillable = [
        'nom_etablissement', 'nom_court', 'telephone', 'email', 'adresse', 'saisie_notes_active', 'portail_parents_actif',
        'deliberation_active', 'deliberation_seuil_admission', 'deliberation_seuil_repechage',
        'deliberation_max_cours_echoues', 'deliberation_note_min_cours',
        'prefet_valide_inscriptions',
        'bulletin_pays', 'bulletin_ministere', 'bulletin_titre', 'bulletin_reference',
        'bulletin_province', 'bulletin_ville', 'bulletin_commune', 'bulletin_code_ecole',
        'bulletin_lieu_signature', 'bulletin_signataire', 'bulletin_seuil_reussite',
        'bulletin_afficher_place', 'bulletin_afficher_conduite',
        'devise', 'taux_change',
        'module_recus_actif', 'module_recouvrement_actif',
    ];

    protected function casts(): array
    {
        return [
            'saisie_notes_active' => 'boolean',
            'portail_parents_actif' => 'boolean',
            'deliberation_active' => 'boolean',
            'prefet_valide_inscriptions' => 'boolean',
            'deliberation_seuil_admission' => 'decimal:2',
            'deliberation_seuil_repechage' => 'decimal:2',
            'deliberation_max_cours_echoues' => 'integer',
            'deliberation_note_min_cours' => 'decimal:2',
            'bulletin_seuil_reussite' => 'decimal:2',
            'bulletin_afficher_place' => 'boolean',
            'bulletin_afficher_conduite' => 'boolean',
            'taux_change' => 'decimal:2',
            'module_recus_actif' => 'boolean',
            'module_recouvrement_actif' => 'boolean',
        ];
    }

    /** Module « reçu » : page imprimable du reçu de paiement. Actif par défaut. */
    public static function recusActifs(): bool
    {
        return static::drapeauModule('module_recus_actif');
    }

    /** Module « recouvrement » : tableau de suivi des encaissements. Actif par défaut. */
    public static function recouvrementActif(): bool
    {
        return static::drapeauModule('module_recouvrement_actif');
    }

    /**
     * L'administration a-t-elle autorisé le Préfet des Études à valider les
     * demandes d'inscription ? Faux par défaut (colonne absente ou décochée).
     */
    public static function prefetValideInscriptions(): bool
    {
        $reglages = static::current();

        if (! array_key_exists('prefet_valide_inscriptions', $reglages->getAttributes())) {
            return false;
        }

        return (bool) $reglages->prefet_valide_inscriptions;
    }

    /**
     * Lit un commutateur de module : absent (base non migrée) ou non configuré
     * signifie « actif », pour ne rien casser par défaut.
     */
    private static function drapeauModule(string $colonne): bool
    {
        $reglages = static::current();

        if (! array_key_exists($colonne, $reglages->getAttributes())) {
            return true;
        }

        return (bool) $reglages->{$colonne};
    }

    /** Taux de change par défaut : CDF pour 1 USD (2800 si non configuré). */
    public static function tauxChange(): float
    {
        $taux = (float) (static::current()->taux_change ?? 0);

        return $taux > 0 ? $taux : Monnaie::TAUX_DEFAUT;
    }

    /**
     * Réglages en vigueur (ligne unique), ou valeurs par défaut si aucune n'existe.
     */
    public static function current(): self
    {
        try {
            return static::query()->first() ?? static::defaults();
        } catch (\Throwable) {
            // Table absente ou base indisponible (installation en cours, test isolé) :
            // les valeurs par défaut sont utilisées plutôt que de faire échouer la page.
            return static::defaults();
        }
    }

    /** Seuil de réussite du bulletin, configuré par l'administration (50 % par défaut). */
    public static function seuilReussiteBulletin(): float
    {
        $seuil = static::current()->bulletin_seuil_reussite;

        return $seuil === null || (float) $seuil <= 0 ? self::SEUIL_REUSSITE_DEFAUT : (float) $seuil;
    }

    public static function defaults(): self
    {
        return new self([
            'nom_etablissement' => 'COMPLEXE SCOLAIRE BABAZIN',
            'nom_court' => 'C.S. Moty',
            'saisie_notes_active' => true,
            'portail_parents_actif' => false,
            'bulletin_pays' => 'République Démocratique du Congo',
            'bulletin_ministere' => "Ministère de l'Enseignement Primaire, Secondaire et Professionnel",
            // Titre déduit du cycle réel de la classe (voir le bulletin) ;
            // l'établissement peut le fixer dans les Paramètres du bulletin.
            'bulletin_titre' => null,
            'bulletin_reference' => 'EBE/P.S./010',
            'bulletin_signataire' => 'Le Chef d’Établissement',
            'bulletin_seuil_reussite' => self::SEUIL_REUSSITE_DEFAUT,
            'bulletin_afficher_place' => true,
            'bulletin_afficher_conduite' => true,
        ]);
    }
}
