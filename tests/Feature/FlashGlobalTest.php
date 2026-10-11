<?php

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('a flash message is shown globally on a page that does not render it inline', function () {
    $this->withSession(['error' => 'Votre compte a été désactivé.'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('id="flash-global"', false)
        ->assertSee('Votre compte a été désactivé.');
});

test('a message already rendered inline is not repeated by the global banner', function () {
    $this->withSession(['success' => 'Vous êtes déconnecté.'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Vous êtes déconnecté.')
        ->assertDontSee('id="flash-global"', false);
});

test('validation errors are surfaced globally when the page does not show them', function () {
    $sac = new ViewErrorBag();
    $sac->put('default', new MessageBag(['Le montant est obligatoire.']));

    $this->withSession(['errors' => $sac])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('id="flash-global"', false)
        ->assertSee('Le montant est obligatoire.');
});

test('a page without any flash message is left untouched', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('id="flash-global"', false);
});
