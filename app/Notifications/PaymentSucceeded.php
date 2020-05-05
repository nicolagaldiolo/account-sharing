<?php

namespace App\Notifications;

use App\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentSucceeded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $invoice;
    public $userType;

    public function __construct(Invoice $invoice, $userType)
    {
        $this->invoice = $invoice;
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
                    ->subject(config('app.name') . ' - Pagamento Effettuato per condivisione ' . $this->invoice->service_name)
                    ->line('Ciao ' . $notifiable->username . ', il pagamento di ' . $this->invoice->currency . ' ' . $this->invoice->total . ' per la condivisione ' . $this->invoice->service_name . ' è stato effettuato con successo.');
                break;
            case 'OWNER':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Pagamento Ricevuto per condivisione ' . $this->invoice->service_name)
                    ->line('Ciao ' . $notifiable->username . ', hai ricevuto un pagamento di ' . $this->invoice->currency . ' ' . $this->invoice->total . ' da parte di ' . $this->invoice->user->username . ' per la tua condivisione ' . $this->invoice->service_name . '.');
                break;
            case 'ADMIN':
                return (new MailMessage)
                    ->subject(config('app.name') . ' - Pagamento Ricevuto per condivisione ' . $this->invoice->service_name)
                    ->line('Ciao ' . $notifiable->username . ', l\'utente ' . $this->invoice->user->username . 'ha inviato un pagamento di ' . $this->invoice->currency . ' ' . $this->invoice->total . ' per la condivisione ' . $this->invoice->service_name . '.');
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
