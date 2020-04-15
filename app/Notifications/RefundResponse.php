<?php

namespace App\Notifications;

use App\Enums\RefundApplicationStatus;
use App\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RefundResponse extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $refund;
    public $userType;

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

        switch ($this->refund->internal_status){
            case RefundApplicationStatus::Approved:
                switch ($this->userType){
                    case 'USER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Rimborso accettato')
                            ->line('La tua richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . ' è stata accettata.')
                            ->line('Abbiamo emesso un rimborso di ' . $this->refund->currency . ' ' . $this->refund->total . ' a tuo favore che saranno riaccreditati sul metodo di pagamento che hai utilizzato in 5-7 giorni lavorativi (tempistiche bancarie)');
                        break;
                    case 'OWNER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Richiesta di rimborso di ' . $this->refund->user->username . ' accettata')
                            ->line('Ciao ' . $this->refund->owner->username . ', abbiamo valutato le motivazioni fornite da ' . $this->refund->user->username . ', che faceva parte del tuo gruppo di ' . $this->refund->service . ' e abbiamo accettato la sua richiesta di rimborso')
                            ->line('Fai attenzione! Se il tuo gruppo genera troppe richieste di rimborso, perderai la fiducia degli altri utenti e potresti ricevere delle penalità.')
                            ->line('Buona condivisione');
                        break;
                    case 'ADMIN':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Rimborso accettato')
                            ->line('La richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . ' è stata accettata.');
                        break;
                }
                break;
            case RefundApplicationStatus::Refused:
                switch ($this->userType){
                    case 'USER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Rimborso rifiutato')
                            ->line('La tua richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . ' è stata rifiutata.');
                        break;
                    case 'OWNER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Richiesta di rimborso di ' . $this->refund->user->username . ' rifiutata')
                            ->line('Ciao ' . $this->refund->owner->username . ', abbiamo valutato le motivazioni fornite da ' . $this->refund->user->username . ', che fa parte del tuo gruppo di ' . $this->refund->service . ' e abbiamo rifiutato la sua richiesta di rimborso')
                            ->line('Buona condivisione');
                        break;
                    case 'ADMIN':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Rimborso rifiutato')
                            ->line('La richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . ' è stata rifiutata.');
                        break;
                }
                break;
            case RefundApplicationStatus::Pending:
                switch ($this->userType){
                    case 'USER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Richiesta di rimborso di annullata')
                            ->line('La tua richiesta di rimborso per la condivisione ' . $this->refund->service . ' di ' . $this->refund->owner->username . ' è stata annullata coma da tua richiesta.');
                        break;
                    case 'OWNER':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Richiesta di rimborso di ' . $this->refund->user->username . ' annullata')
                            ->line('Ciao ' . $this->refund->owner->username . ', la richiesta di rimborso di ' . $this->refund->user->username . ' per la tua condivisione ' . $this->refund->service . ' è stata annullata.')
                            ->line('Buona condivisione');
                        break;
                    case 'ADMIN':
                        return (new MailMessage)
                            ->subject(config('app.name') . ' - Richiesta di rimborso di ' . $this->refund->user->username . ' annullata')
                            ->line($this->refund->user->username . ' ha annullato la richiesta di rimborso per il gruppo ' . $this->refund->service .' di ' . $this->refund->owner->username . '.');
                        break;
                }
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
