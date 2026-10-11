<?php

test('an unknown page shows the custom 404 error page', function () {
    $this->get('/cette-page-nexiste-vraiment-pas')
        ->assertStatus(404)
        ->assertSee('404')
        ->assertSee('Page introuvable')
        ->assertSee('Retour à l’accueil');
});

test('the custom error pages render their French message', function () {
    foreach ([
        '403' => 'Accès refusé',
        '404' => 'Page introuvable',
        '419' => 'Session expirée',
        '429' => 'Trop de requêtes',
        '500' => 'Erreur interne du serveur',
        '503' => 'Service indisponible',
    ] as $code => $expected) {
        $this->view("errors.$code")->assertSee($code)->assertSee($expected);
    }
});
