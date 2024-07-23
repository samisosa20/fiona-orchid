<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Support;

class SupportCreated extends Notification
{
    use Queueable;

    protected $support;

    /**
     * Create a new notification instance.
     */
    public function __construct(Support $support)
    {
        $this->support = $support;
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
            ->subject("EMMA - Soporte creado")
            ->line('Has creado una nueva solicitud de soporte. Nuestro equipo estará ayudándote con tu solicitud lo antes posible.')
            ->line('Subject: ' . $this->support->subject)
            ->line('Message: ' . $this->support->message)
            ->line('No responder a este correo')
            ->line('Gracias por contactarnos!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
