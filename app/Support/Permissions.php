<?php

namespace App\Support;

/**
 * Lecture et application de la matrice des permissions (config/permissions.php).
 * Le rôle `admin` est le super-administrateur : il passe toujours.
 */
class Permissions
{
    /** Postes de la matrice, dans l'ordre d'affichage. */
    public static function roles(): array
    {
        return config('permissions.roles', []);
    }

    /** Fonctionnalités de la matrice (code => libellé). */
    public static function features(): array
    {
        return array_map(
            fn (array $feature) => $feature['libelle'] ?? '',
            config('permissions.features', [])
        );
    }

    /** Niveau d'un poste sur une fonctionnalité (« non » par défaut). */
    public static function niveau(?string $role, string $feature): string
    {
        return config("permissions.features.$feature.niveaux.$role", 'non');
    }

    /** Le poste peut-il accéder à la fonctionnalité ? (tout niveau ≠ « non »). */
    public static function autorise(?string $role, string $feature): bool
    {
        if ($role === null) {
            return false;
        }

        if ($role === 'admin') {
            return true;
        }

        return static::niveau($role, $feature) !== 'non';
    }

    /**
     * Droits d'un poste : code fonctionnalité => niveau.
     *
     * @return array<string, string>
     */
    public static function pour(?string $role): array
    {
        $droits = [];

        foreach (array_keys(config('permissions.features', [])) as $code) {
            $droits[$code] = static::niveau($role, $code);
        }

        return $droits;
    }

    /** Libellé lisible d'un niveau. */
    public static function libelle(string $niveau): string
    {
        return config('permissions.labels.'.$niveau) ?? ucfirst($niveau);
    }
}
