<?php

namespace App\Notifications;

use App\Models\Signalement;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatutSignalementChange extends Notification
{
    private const LABELS = [
        'recu'           => 'Reçu',
        'en_examen'      => 'En examen',
        'attribue'       => 'Attribué à un agent',
        'en_instruction' => 'En cours d\'instruction',
        'traite'         => 'Traité',
        'classe'         => 'Classé',
    ];

    public function __construct(
        private readonly Signalement $signalement,
        private readonly string      $ancienStatut,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $code    = $this->signalement->code;
        $nouveau = self::LABELS[$this->signalement->statut] ?? $this->signalement->statut;
        $ancien  = self::LABELS[$this->ancienStatut] ?? $this->ancienStatut;

        $mail = (new MailMessage)
            ->subject("Mise à jour de votre signalement {$code}")
            ->greeting("Bonjour,")
            ->line("Le statut de votre signalement **{$code}** a été mis à jour.")
            ->line("Ancien statut : **{$ancien}**")
            ->line("Nouveau statut : **{$nouveau}**");

        if ($this->signalement->message_agent) {
            $mail->line("Message de l'agent : *{$this->signalement->message_agent}*");
        }

        $mail->line("Vous pouvez suivre l'évolution de votre dossier à tout moment avec votre code de suivi.")
             ->line("Ce message a été envoyé automatiquement — ne pas répondre.");

        return $mail;
    }
}
