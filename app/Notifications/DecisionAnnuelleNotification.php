<?php

namespace App\Notifications;

use App\Models\Deliberation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Résultat de fin d'année d'un élève (admis / ajourné / redouble), déposé dans
 * la base (canal « database ») pour chaque compte parent rattaché : il apparaît
 * dans la cloche et sur « Mes notifications » du portail parent.
 */
class DecisionAnnuelleNotification extends Notification
{
    use Queueable;

    public function __construct(public Deliberation $deliberation)
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
        $inscription = $this->deliberation->inscription;
        $eleve = $inscription?->eleve;
        $classe = $inscription?->classe?->nom;
        $suivante = $this->deliberation->classeSuivante?->nom;
        $decision = $this->deliberation->decision;

        $nom = trim(($eleve?->prenom ?? '').' '.($eleve?->nom ?? '')) ?: 'Votre enfant';

        $message = match ($decision) {
            'admis' => $nom.' passe en classe supérieure'.($suivante ? ' ('.$suivante.')' : '').'.',
            'ajourne' => $nom.' est ajourné(e) : le conseil de classe statuera sur le repêchage.',
            default => $nom.' redouble'.($classe ? ' la '.$classe : ' la classe').'.',
        };

        return [
            'titre' => 'Résultat de fin d\'année'.($classe ? ' — '.$classe : ''),
            'message' => $message,
            'niveau' => match ($decision) {
                'admis' => 'success',
                'ajourne' => 'warning',
                default => 'danger',
            },
            'lien' => $eleve ? route('parent.enfants', ['enfant' => $eleve->id]) : route('parent.dashboard'),
            'eleve_id' => $eleve?->id,
            'decision' => $decision,
            'auteur' => $this->deliberation->decidePar?->username ?? 'Administration',
        ];
    }
}
