<?php

namespace App\Notifications;

use App\Models\Annonce;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification d'annonce, déposée dans la base (canal « database ») pour
 * chaque destinataire : elle apparaît dans la cloche et sur la page
 * « Mes notifications ».
 */
class AnnonceNotification extends Notification
{
    use Queueable;

    public function __construct(public Annonce $annonce)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'annonce_id' => $this->annonce->id,
            'titre' => $this->annonce->titre,
            'message' => $this->annonce->message,
            'niveau' => $this->annonce->niveau,
            'lien' => $this->annonce->lien,
            'cible' => $this->annonce->cible,
            'auteur' => $this->annonce->auteur?->username ?? 'Administration',
        ];
    }
}
