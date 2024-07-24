<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\SupportResponse;

class SupportResponseNotification extends Notification
{
    use Queueable;

    protected $supportResponse;

    /**
     * Create a new notification instance.
     */
    public function __construct(SupportResponse $supportResponse)
    {
        $this->supportResponse = $supportResponse;
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
            ->subject('EMMA - Respuesta de tu soporte')
            ->view('emails.support_response', [
                'htmlContent' => $this->supportResponse->content,
                'name' => $this->supportResponse->support->user->name,
            ]);
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
