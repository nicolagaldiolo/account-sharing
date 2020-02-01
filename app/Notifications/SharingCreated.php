<?php

namespace App\Notifications;

use App\Enums\SharingApprovationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SharingCreated extends Notification implements ShouldQueue
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
        return ['mail'];
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

        $sharing_url = url(config('app.url') . "/category/" . $this->sharing->category->id . "/sharing/" . $this->sharing->id);

        if($this->sharing->status === SharingApprovationStatus::Pending){
            $message->subject(config('app.name') . ' - Nuovo gruppo di condivisione creato, *** NECESSARIA APPROVAZIONE ***')
                ->greeting('Ciao ' . $notifiable->name)
                ->line($this->sharing->owner->username . ' ha creato un nuovo gruppo (' . $this->sharing->name . '), necessaria approvazione.')
                ->action('Verifica Gruppo', $sharing_url);
        }else{
            $message->subject(config('app.name') . ' - Nuovo gruppo di condivisione creato')
                ->greeting('Ciao ' . $notifiable->name)
                ->line($this->sharing->owner->username . ' ha creato un nuovo gruppo (' . $this->sharing->name . ')')
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
