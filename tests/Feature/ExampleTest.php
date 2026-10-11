<?php

// La page d'ouverture du système est la vitrine publique (`/`), couverte par
// PublicSiteTest avec son schéma de base de données. Ici, on vérifie simplement
// que l'écran de connexion reste accessible.
test('the login screen returns a successful response', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});
