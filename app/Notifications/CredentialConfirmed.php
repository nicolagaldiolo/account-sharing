<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Http\Resources\Sharing as SharingResource;
use Illuminate\Support\Facades\Auth;

class CredentialConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $user;
    public $sharing;

    public function __construct($user, $sharing)
    {
        $this->user = $user;
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
            ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Credenziali confermate')
            ->greeting('Ciao ' . $notifiable->name)
            ->line($this->user->name . ' ha appena confermato che le credenziali di ' . $this->sharing->name . ' che hai salvato sono valide.')
            ->line('Ora tutti gli utenti di ' . config('app.name') . ' sapranno che sei un admin affidabile.');
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
        return new BroadcastMessage([
            'data' => new SharingResource($this->sharing)
        ]);
    }
}
