<?php

namespace App\Notifications;

use App\Enums\SharingApprovationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SharingStatusUpdated extends Notification implements ShouldQueue
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

        $sharing_url = url(config('app.url') . "/category/" . $this->sharing->category->id . "/sharing/" . $this->sharing->id);

        switch ($this->sharing->status){
            case SharingApprovationStatus::Pending:
                return (new MailMessage)
                    ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Gruppo in fase di verifica')
                    ->greeting('Ciao ' . $notifiable->name)
                    ->line("La condivisione {$this->sharing->name} che hai creato è in fase di approvazione, attendere comunicazione da parte dello staff.")
                    ->action('Vai al gruppo', $sharing_url)
                    ->line('Thank you for using our application!');
                break;
            case SharingApprovationStatus::Approved:
                return (new MailMessage)
                    ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Gruppo approvato')
                    ->greeting('Ciao ' . $notifiable->name)
                    ->line("La condivisione {$this->sharing->name} che hai creato è stata approvata, inizia a condividere.")
                    ->action('Vai al gruppo', $sharing_url)
                    ->line('Thank you for using our application!');
                break;
            case SharingApprovationStatus::Refused:
                return (new MailMessage)
                    ->subject(config('app.name') . ': ' . $this->sharing->name . ' - Gruppo rifiutato')
                    ->greeting('Ciao ' . $notifiable->name)
                    ->line("La condivisione {$this->sharing->name} che hai creato è stata rifiutata in quanto non rispetta i termini di utilizzo.")
                    ->line('Thank you for using our application!');
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
