<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Newsletter;

class SendNewsLetterEmail extends Notification
{
    use Queueable;

    protected $newslwtter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Newsletter $newslwtter)
    {
        $this->newslwtter = $newslwtter;
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
            ->subject('EMMA - ' . $this->newslwtter->subject)
            ->markdown('mail.newsletter_email', [
                'content' => $this->newslwtter->content,
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
