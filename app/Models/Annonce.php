<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Annonce diffusée par l'administration à tout ou partie des utilisateurs.
 */
class Annonce extends Model
{
    public const NIVEAUX = [
        'info' => 'Information',
        'success' => 'Bonne nouvelle',
        'warning' => 'Avertissement',
        'danger' => 'Urgent',
    ];

    public const CIBLES = [
        'tous' => 'Tous les utilisateurs',
        'admin' => 'Administrateurs',
        'prefetture' => 'Préfecture',
        'titulaire' => 'Titulaires',
        'professeur' => 'Professeurs',
        'comptable' => 'Comptables',
        'parent' => 'Parents',
    ];

    protected $fillable = ['titre', 'message', 'niveau', 'lien', 'cible', 'envoye_par', 'destinataires'];

    public function auteur()
    {
        return $this->belongsTo(User::class, 'envoye_par');
    }

    public function libelleNiveau(): string
    {
        return self::NIVEAUX[$this->niveau] ?? 'Information';
    }

    public function libelleCible(): string
    {
        return self::CIBLES[$this->cible] ?? 'Tous les utilisateurs';
    }
}
