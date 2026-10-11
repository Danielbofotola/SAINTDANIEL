<?php

namespace App\Support;

use App\Models\Classe;

/**
 * Identité « réelle » d'une classe pour l'en-tête des documents : cycle, section
 * et option, en écartant les redites (ex. option identique à la section, ou
 * section qui reprend déjà le cycle).
 */
class StructureClasse
{
    /** Libellé « cycle — section — option », dédoublonné, ou chaîne vide. */
    public static function libelle(?Classe $classe): string
    {
        if ($classe === null) {
            return '';
        }

        $gardees = [];
        $normes = [];

        foreach ([$classe->cycle?->nom, $classe->section?->nom, $classe->option?->nom] as $valeur) {
            if (! filled($valeur)) {
                continue;
            }

            $norme = self::normaliser($valeur);

            if ($norme === '') {
                continue;
            }

            // On écarte une valeur déjà présente ou contenue dans une autre
            // (comparaison insensible à la casse, aux espaces et à la ponctuation).
            foreach ($normes as $deja) {
                if (str_contains($deja, $norme) || str_contains($norme, $deja)) {
                    continue 2;
                }
            }

            $gardees[] = $valeur;
            $normes[] = $norme;
        }

        return implode(' — ', $gardees);
    }

    private static function normaliser(string $valeur): string
    {
        return preg_replace('/[^a-z0-9]+/i', '', mb_strtolower($valeur)) ?? '';
    }
}
