<?php

/**
 * Page d'amorçage (création du premier compte administrateur).
 *
 * `token` : jeton secret exigé dans l'URL de la page (`?cle=…`). Laisser vide
 * pour ne dépendre que de la règle « aucun administrateur n'existe ».
 */
return [
    'token' => env('SYSTEME_INSTALL_TOKEN'),
];
