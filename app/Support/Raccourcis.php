<?php

namespace App\Support;

use App\Models\User;

/**
 * Raccourcis de l'en-tête (modèle du template) : un lien direct suivi d'un
 * menu déroulant « Rapports », adaptés au rôle de l'utilisateur connecté.
 */
class Raccourcis
{
    /**
     * @return array{direct: array{libelle: string, icone: string, url: string}, rapports: array<int, array{0: string, 1: string, 2: string}>}
     */
    public static function pour(?User $utilisateur): array
    {
        $role = $utilisateur?->role ?? 'invite';

        return match ($role) {
            'admin' => [
                'direct' => ['libelle' => 'Documents', 'icone' => 'fa-file-text-o', 'url' => route('admin.eleves.index')],
                'rapports' => [
                    ['Rapports journaliers', 'fa-line-chart', route('admin.statistiques.index')],
                    ['Comptabilité OHADA', 'fa-calculator', route('admin.comptabilite.index')],
                    ['Évaluations & examens', 'fa-pencil-square-o', route('admin.evaluations.index')],
                    ['Paie & salaires', 'fa-money', route('admin.paies.index')],
                    ['Délibération & passage', 'fa-gavel', route('admin.deliberations.index')],
                    ['Messagerie (modération)', 'fa-comments', route('admin.messagerie.index')],
                ],
            ],
            'professeur' => [
                'direct' => ['libelle' => 'Mes élèves', 'icone' => 'fa-users', 'url' => route('professeur.eleves')],
                'rapports' => [
                    ['Mon planning', 'fa-calendar-o', route('professeur.planning')],
                    ['Mes cours', 'fa-book', route('professeur.cours')],
                    ['Évaluations & notes', 'fa-pencil-square-o', route('professeur.evaluations')],
                    ['Feuilles de présence', 'fa-check-square-o', route('professeur.presences')],
                ],
            ],
            'prefetture' => [
                'direct' => ['libelle' => 'Mon espace', 'icone' => 'fa-dashboard', 'url' => route('prefecture.dashboard')],
                'rapports' => [],
            ],
            'titulaire' => [
                'direct' => ['libelle' => 'Mon espace', 'icone' => 'fa-dashboard', 'url' => route('titulaire.dashboard')],
                'rapports' => [
                    ['Mon tableau de bord', 'fa-dashboard', route('titulaire.dashboard')],
                ],
            ],
            'comptable' => [
                'direct' => ['libelle' => 'Paiements', 'icone' => 'fa-money', 'url' => route('comptable.paiements.index')],
                'rapports' => [
                    ['Dépenses & natures', 'fa-shopping-cart', route('comptable.depenses.index')],
                    ['Paie & salaires', 'fa-money', route('comptable.paies.index')],
                ],
            ],
            'parent' => [
                'direct' => ['libelle' => 'Espace Parent', 'icone' => 'fa-users', 'url' => route('parent.dashboard')],
                'rapports' => [],
            ],
            default => [
                'direct' => ['libelle' => 'Tableau de bord', 'icone' => 'fa-dashboard', 'url' => route('login')],
                'rapports' => [],
            ],
        };
    }
}
