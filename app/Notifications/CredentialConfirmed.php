<?php

namespace App\Notifications;

use App\Enums\CredentialsStatus;
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
    public $action;
    public $actionDesc;

    public function __construct($user, $sharing, $action)
    {
        $this->user = $user;
        $this->sharing = $sharing;
        $this->action = $action;

        if($this->action === CredentialsStatus::Confirmed){
            $this->actionDesc = $this->user->name . ' ha appena confermato che le credenziali di ' . $this->sharing->name . ' che hai salvato sono valide.';
        }else if ($this->action === CredentialsStatus::Wrong) {
            $this->actionDesc = $this->user->name . ' ha appena confermato che le credenziali di ' . $this->sharing->name . ' che hai salvato sono errate.';
        }else{
            $this->actionDesc = '';
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->getChannels($notifiable);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if($this->action === CredentialsStatus::Confirmed){
            return (new MailMessage)
                ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Credenziali confermate')
                ->greeting('Ciao ' . $notifiable->name)
                ->line($this->actionDesc)
                ->line('Ora tutti gli utenti di ' . config('app.name') . ' sapranno che sei un admin affidabile.');
        }else if ($this->action === CredentialsStatus::Wrong) {
            return (new MailMessage)
                ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Credenziali errate')
                ->greeting('Ciao ' . $notifiable->name)
                ->line($this->actionDesc)
                ->line('Ti preghiamo di fornire delle credenziali corrette.');
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
            'icon' => $this->sharing->image,
            'desc' => $this->actionDesc,
            'data' => [
                'sharing' => new SharingResource($this->sharing, $notifiable),
                'action' => $this->action
            ]
        ];
    }

    protected function getChannels($notifiable)
    {
        $channels = [
            'broadcast'
        ];

        if($notifiable->id === $this->sharing->owner->id || !$this->sharing->members->pluck('id')->contains($this->user->id)){
            array_push($channels, 'mail', 'database');
        }

        return $channels;
    }
}
