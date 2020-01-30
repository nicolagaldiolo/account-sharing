<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AskCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $sharing;
    public $user;

    public function __construct($sharing, $user)
    {
        $this->sharing = $sharing;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail'
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Richiesta credenziali')
            ->greeting('Ciao ' . $notifiable->name)
            ->line($this->user->name . ' ha appena sollecitato l\'invio delle credenziali per la condivisione ' . $this->sharing->name . '.')
            ->action('Aggiungi credenziali', url(config('app.url') . "/category/" . $this->sharing->category->id . "/sharing/" . $this->sharing->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
