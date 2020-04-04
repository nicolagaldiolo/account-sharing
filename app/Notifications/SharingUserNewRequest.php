<?php

namespace App\Notifications;

use App\Sharing;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SharingUserNewRequest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $sharing;
    public $user;
    public $forOwner;

    public function __construct(Sharing $sharing, User $user, $forOwner = false)
    {
        $this->sharing = $sharing;
        $this->user = $user;
        $this->forOwner = $forOwner;
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
        $message = new MailMessage;

        if($this->forOwner){

            $sharing_url = url(config('app.url') . "/sharings/owner");

            $message->subject(config('app.name') . ' - Nuova richiesta di partecipazione al tuo gruppo di condivisione ' . $this->sharing->name)
                ->greeting('Ciao ' . $notifiable->name)
                ->line($this->user->username . ' chiede di poter entrare a far parte del tuo gruppo di condivisione ' . $this->sharing->name . '.')
                ->line('Autorizza o rifiuta la richiesta.')
                ->action('Gestisci richiesta', $sharing_url);
        }else{

            $sharing_url = url(config('app.url') . "/category/" . $this->sharing->category_id . "/sharing/" . $this->sharing->id);

            $message->subject(config('app.name') . ' - Richiesta di partecipazione al gruppo di condivisione ' . $this->sharing->name)
                ->greeting('Ciao ' . $notifiable->name)
                ->line('La tua richiesta di partecipazione al gruppo di condivisione ' . $this->sharing->name . ' è in fase di approvazione.')
                ->action('Vai al gruppo', $sharing_url);
        }

        $message->line('Thank you for using our application!');

        return $message;
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
        if($this->forOwner) {
            return [
                'icon' => $this->user->photo_url,
                'desc' => $this->user->username . ' chiede di poter entrare a far parte del tuo gruppo di condivisione ' . $this->sharing->name,
            ];
        } else {
            return [
                'icon' => $this->sharing->image,
                'desc' => 'La tua richiesta di partecipazione al gruppo di condivisione ' . $this->sharing->name . ' è in fase di approvazione.',
            ];
        }
    }
}
