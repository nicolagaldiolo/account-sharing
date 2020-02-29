<?php

namespace App\Notifications;

use App\Sharing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SharingUserUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $sharing;
    public $status;

    public function __construct(Sharing $sharing, $status)
    {
        $this->sharing = $sharing;
        $this->status = $status;
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
            'database',
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
        $sharing_url = url(config('app.url') . "/category/" . $this->sharing->category_id . "/sharing/" . $this->sharing->id);

        return (new MailMessage)
            ->subject(config('app.name') . $this->sharing->name . '- Richiesta ' . $this->status)
            ->greeting('Ciao ' . $notifiable->name)
            ->line('Ti comunichiamo che la tua richiesta di far parte del gruppo ' . $this->sharing->name . ' è in stato ' . $this->status)
            ->action('Vai al gruppo', $sharing_url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // The default method for Database e Broadcast channel, otherwise must create the toDatabase() and toBroadcast() methods
    public function toArray($notifiable)
    {
        return [
            'icon' => $this->sharing->image,
            'desc' => 'La tua richiesta di partecipazione al gruppo di condivisione ' . $this->sharing->name . ' è in stato ' . $this->status
        ];
    }
}
