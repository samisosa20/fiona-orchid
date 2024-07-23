<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Support;
use App\Models\User;

class SupportCopyCreated extends Notification
{
    use Queueable;

    protected $support;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(Support $support, User $user)
    {
        $this->support = $support;
        $this->user = $user;
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
                    ->line('Se ha creado una nueva solicitud de soporte:')
                    ->line('Usuario: ' . $this->user->name)
                    ->line('Email del usuario: ' . $this->user->email)
                    ->line('Subject: ' . $this->support->subject)
                    ->line('Message: ' . $this->support->message)
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
