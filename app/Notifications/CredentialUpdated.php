<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CredentialUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $sharing;

    public function __construct($sharing)
    {
        $this->sharing = $sharing;
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
            'mail',
            'broadcast'
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
                    ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Credenziali aggiornate')
                    ->greeting('Ciao ' . $notifiable->name)
                    ->line("Le credenziali della condivisione {$this->sharing->name} sono state aggiornate. Accedi all'app e verifica le credenziali")
                    ->action('Verifica credenziali', url(config('app.url') . "/category/" . $this->sharing->category->id . "/sharing/" . $this->sharing->id))
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

    public function toBroadcast($notifiable)
    {
        //logger($this->sharing);
        return new BroadcastMessage([
            'data' => $this->sharing
        ]);
    }
}
