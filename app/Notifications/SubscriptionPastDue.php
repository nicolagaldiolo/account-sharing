<?php

namespace App\Notifications;

use App\Sharing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionPastDue extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $sharing;
    public $stripeSubscription;

    public function __construct(Sharing $sharing, $stripeSubscription)
    {
        $this->sharing = $sharing;
        $this->stripeSubscription = $stripeSubscription;
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

        $url = url(config('app.url') . "/category/" . $this->sharing->category_id . "/sharing/" . $this->sharing->id);

        return (new MailMessage)
            ->subject(config('app.name') . ' - Pagamento Rifiutato per condivisione ' . $this->sharing->name)
            ->line('Ciao ' . $notifiable->username . ', il pagamento di ' . $this->stripeSubscription['currency'] . ' ' . $this->stripeSubscription['total'] . ' per la condivisione ' . $this->sharing->name . ' non è andato a buon fine.')
            ->action('Modifica metodo di pagamento', $url)
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
