<?php

namespace App\Notifications;

use App\SharingUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionDeleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $sharingUser;
    public $userType;
    public $sharing;

    public function __construct(SharingUser $sharingUser, $userType)
    {
        $this->sharingUser = $sharingUser;
        $this->userType = $userType;
        $this->sharing = $this->sharingUser->sharing;
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
        switch ($this->userType){
            case 'USER':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Hai abbandonato il gruppo di condivisione ' . $this->sharing->name)
                    ->line('Ciao ' . $notifiable->username . ', sei uscito dal gruppo di condivisione ' . $this->sharing->name . ' di ' . $this->sharing->owner->username)
                    ->line('Speriamo di riaverti al più presto');
                break;
            case 'OWNER':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Un utente ha abbandonato il gruppo di condivisione: ' . $this->sharing->name)
                    ->line('Ciao ' . $notifiable->username . ', l\'utente ' . $this->sharingUser->user->username . ' è uscito dal tuo gruppo di condivisione ' . $this->sharing->name)
                    ->line('Ricordati di cambiare la password del servizio');
                break;
            case 'ADMIN':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Utente ha abbandonato il gruppo ' . $this->sharing->name)
                    ->line('Ciao ' . $notifiable->username . ', l\'utente ' . $this->sharingUser->user->username . ' è uscito dal gruppo di condivisione ' . $this->sharing->name);
                break;
        }
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
