<?php

namespace App\Notifications;

use App\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RefundNewRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public $refund;
    public $userType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Refund $refund, $userType)
    {
        $this->refund = $refund;
        $this->userType = $userType;
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
        switch ($this->userType){
            case 'USER':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Richiesta di rimborso inviata')
                    ->line('Abbiamo ricevuto la tua richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . '.')
                    ->line('Riceverai una mail appena ci saranno aggiornamenti.');
                break;
            case 'OWNER':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Richiesta di rimborso per il tuo gruppo ' . $this->refund->service)
                    ->line($this->refund->user->username . ' ha richiesto un rimborso per il gruppo di ' . $this->refund->service . ' di cui sei admin.')
                    ->line('La motivazione che ha fornito è la seguente: ')
                    ->line($this->refund->reason);
                break;
            case 'ADMIN':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Richiesta di rimborso per il gruppo ' . $this->refund->service)
                    ->line($this->refund->user->username . ' ha richiesto un rimborso per il gruppo di ' . $this->refund->service . '.')
                    ->line('La motivazione che ha fornito è la seguente: ')
                    ->line($this->refund->reason);
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
