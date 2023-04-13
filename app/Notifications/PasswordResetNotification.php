<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($password)
    {
        $this->new_password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu contraseña ha sido restablecida')
            ->greeting('¡Hola!')
            ->line('Tu contraseña ha sido restablecida correctamente.')
            ->line("Tu nueva contraseña es: {$this->new_password}")
            ->line('Si no solicitaste el restablecimiento de la contraseña, por favor ignora este mensaje.');
    }

}
