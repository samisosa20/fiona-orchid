<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderLoginEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $list;

    /**
     * Create a new notification instance .
     *
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('¡Te extrañamos!¡Solo necesitas 10 minutos a la semana! - FionaApp')
            ->greeting('¡Hola!')
            ->line('Esperamos que te encuentres bien y disfrutando de tus días. ¿Sabías que te hemos estado extrañando en Fiona?')
            ->line('¡No te preocupes! No estamos aquí para reñirte, ¡solo queremos verte más a menudo! 😄 Sabemos que la vida puede ser ocupada, pero sacar solo 10 minutos a la semana para reportar puede hacer una gran diferencia.')
            ->line('¡Y estamos seguros de que esos 10 minutos serán los más divertidos de tu semana! Puedes hacerlo mientras tomas un café, escuchas tu canción favorita o incluso cuando te tomas un merecido descanso.')
            ->line('Si tienes alguna pregunta o necesitas ayuda, no dudes en ponerte en contacto con nuestro equipo de soporte. Estamos aquí para hacer que esta experiencia sea lo más sencilla y agradable posible.')
            ->action('Ir a FIONA', url('https://fiona.itpmsoftware.com'))
            ->line('¡Diviértete y cuídate!')
            ->salutation('Atentamente, Fiona');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }


}