<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCode extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código de verificación de acceso')
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('Usa el siguiente código para confirmar tu inicio de sesión:')
            ->line("Código: **{$this->code}**")
            ->line('Caduca en 10 minutos. Si no solicitaste este acceso, ignora este mensaje.');
    }
}