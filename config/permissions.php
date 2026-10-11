<?php

/**
 * Matrice des permissions par poste : source unique des droits, affichée sur la
 * page « Matrice des permissions » et appliquée par le middleware `permission`.
 *
 * Niveaux : total, oui, non, lecture, avis, suivi, validation, rapports.
 * Tout niveau autre que « non » autorise l'accès à l'écran (consultation et
 * modification). Le rôle `admin` reste le super-administrateur (accès total).
 */
return [
    // Postes de la matrice, dans l'ordre d'affichage.
    'roles' => ['systeme', 'prefetture', 'directeur_etudes', 'secretaire', 'comptable', 'professeur', 'disciplinaire'],

    'labels' => [
        'total' => 'Total',
        'oui' => 'Oui',
        'non' => 'Non',
        'lecture' => 'Lecture',
        'avis' => 'Avis',
        'suivi' => 'Suivi',
        'validation' => 'Validation',
        'rapports' => 'Rapports',
    ],

    'features' => [
        'modules' => [
            'libelle' => 'Commuter les modules (Saisie Notes, Portail Parents)',
            'niveaux' => ['systeme' => 'total'],
        ],
        'decisions' => [
            'libelle' => 'Publier des Décisions Exécutives & Injonctions',
            // Le Préfet publie les décisions/injonctions (§4) ; le Système peut aussi.
            'niveaux' => ['systeme' => 'oui', 'prefetture' => 'oui'],
        ],
        'bulletins_validation' => [
            'libelle' => 'Validation Finale des Bulletins Trimestriels',
            'niveaux' => ['systeme' => 'oui', 'prefetture' => 'avis'],
        ],
        'emplois_du_temps' => [
            'libelle' => 'Gestion & Modification des Emplois du Temps',
            'niveaux' => ['systeme' => 'lecture', 'prefetture' => 'oui'],
        ],
        'saisie_notes' => [
            'libelle' => 'Saisie des Notes & Remplissage Cahier de Texte',
            'niveaux' => ['prefetture' => 'suivi', 'professeur' => 'oui'],
        ],
        'inscriptions' => [
            'libelle' => 'Inscriptions & Création des Dossiers Élèves',
            'niveaux' => ['systeme' => 'validation', 'directeur_etudes' => 'oui'],
        ],
        'frais_recus' => [
            'libelle' => 'Perception des Frais Scolaires & Émission Reçus',
            // Le poste « Secrétaire » figure à la matrice ; le comptable conserve
            // l'accès à la caisse de l'application.
            'niveaux' => ['systeme' => 'rapports', 'secretaire' => 'oui', 'comptable' => 'oui'],
        ],
        'audit' => [
            'libelle' => "Consultation du Journal d'Audit & Historique Actions",
            'niveaux' => ['systeme' => 'oui'],
        ],
        'flux_academique' => [
            'libelle' => 'Flux académique (ouvrir/fermer l’encodage des points)',
            'niveaux' => ['systeme' => 'oui', 'prefetture' => 'oui'],
        ],
        'discipline' => [
            'libelle' => 'Assiduité & discipline (présences, conduite)',
            'niveaux' => ['prefetture' => 'suivi', 'disciplinaire' => 'oui'],
        ],
    ],
];
