<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\User;

class ReportProgresMonthlyEmail extends Notification
{
    use Queueable;

    protected $user;
    protected $balance;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, $balance)
    {
        $this->user = $user;
        $this->balance = $balance;
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
            ->subject('EMMA - Reporte de balance')
            ->markdown('mail.progress_monthly', [
                'user' => $this->user,
                'balance' => $this->balance,
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
