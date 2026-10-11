<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Rouvre la page d'amorçage (création d'un compte administrateur) pendant
 * quelques minutes, même si un administrateur existe déjà : un jeton temporaire
 * est exigé dans l'URL. Utile pour créer un second administrateur en toute
 * sécurité, sans laisser la page ouverte en permanence.
 */
Artisan::command('systeme:amorcage {--minutes=30}', function () {
    $minutes = max(1, (int) $this->option('minutes'));
    $cle = Str::random(48);

    Cache::put('installation.cle', $cle, now()->addMinutes($minutes));

    $this->info('Page d’amorçage ouverte pour '.$minutes.' minute(s) :');
    $this->line(url('/installation-secrete').'?cle='.$cle);
})->purpose("Ouvre temporairement la page de création d'un compte administrateur");

/*
 * Récupération d'accès administrateur depuis le serveur : crée un compte admin
 * ou réinitialise le mot de passe d'un compte existant (utile en cas d'oubli).
 * Aucun navigateur ni jeton n'est nécessaire : il faut un accès à la machine.
 */
Artisan::command('systeme:admin {--username=} {--email=} {--password=} {--liste}', function () {
    if ($this->option('liste')) {
        $admins = \App\Models\User::query()
            ->where('role', 'admin')
            ->get(['id', 'username', 'email', 'statut'])
            ->toArray();

        if ($admins === []) {
            $this->warn('Aucun administrateur enregistré.');

            return 0;
        }

        $this->table(['id', 'username', 'email', 'statut'], $admins);

        return 0;
    }

    $username = trim((string) ($this->option('username') ?: $this->ask('Identifiant du compte administrateur')));

    if ($username === '') {
        $this->error('Identifiant obligatoire.');

        return 1;
    }

    $existant = \App\Models\User::query()->where('username', $username)->first();
    $email = trim((string) ($this->option('email') ?: ($existant?->email ?? $this->ask('Adresse e-mail'))));

    $motDePasse = (string) ($this->option('password') ?: $this->secret('Mot de passe (8 caractères minimum)'));

    if (strlen($motDePasse) < 8) {
        $this->error('Mot de passe trop court : 8 caractères minimum.');

        return 1;
    }

    if ($existant) {
        $existant->update([
            'password' => $motDePasse,          // hashé automatiquement (cast « hashed »)
            'email' => $email !== '' ? $email : $existant->email,
            'role' => 'admin',
            'statut' => 'actif',
        ]);

        $this->info('Administrateur « '.$username.' » mis à jour : mot de passe réinitialisé.');

        return 0;
    }

    if ($email === '') {
        $this->error('Adresse e-mail obligatoire pour un nouveau compte.');

        return 1;
    }

    \App\Models\User::create([
        'username' => $username,
        'email' => $email,
        'password' => $motDePasse,
        'role' => 'admin',
        'statut' => 'actif',
    ]);

    $this->info('Administrateur « '.$username.' » créé.');

    return 0;
})->purpose('Crée un compte administrateur ou réinitialise son mot de passe');
