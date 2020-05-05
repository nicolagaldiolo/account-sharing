<?php

namespace App\Notifications;

use App\Http\Resources\Sharing as SharingResource;
use App\Sharing;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SharingNewMember extends Notification implements ShouldQueue
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

        $message = new MailMessage;

        $sharing_url = url(config('app.url') . "/category/" . $this->sharing->category_id . "/sharing/" . $this->sharing->id);

        if($this->forOwner){
            $message->subject(config('app.name') . ' - Nuovo membro nel tuo gruppo di condivisione ' . $this->sharing->name)
                ->greeting('Ciao ' . $notifiable->name)
                ->line('Ora ' . $this->user->username . ' fa parte del tuo gruppo di condivisione ' . $this->sharing->name . '.')
                ->line('Se non lo hai già fatto fornisci le credenziali per usufruire del servizio.')
                ->action('Vai al gruppo', $sharing_url);
        }else{
            $message->subject(config('app.name') . ' - Benvenuto nel gruppo ' . $this->sharing->name)
                ->greeting('Ciao ' . $notifiable->name)
                ->line('Ora fai parte del tuo gruppo di condivisione ' . $this->sharing->name . '.')
                ->line($this->sharing->owner->username . ' Vanny ha accettato la tua richiesta di condivisione per ' . $this->sharing->name)
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
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
