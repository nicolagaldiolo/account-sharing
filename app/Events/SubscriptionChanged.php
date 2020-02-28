<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SubscriptionChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    private $sharingSubscription;
    private $subscription;
    public $payload;

    public function __construct($sharingSubscription, $subscription)
    {
        $this->sharingSubscription = $sharingSubscription;
        $this->subscription = $subscription;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        $invoice = \Stripe\Invoice::retrieve([
            'id' => $this->sharingSubscription['latest_invoice'],
            'expand' => [
                'payment_intent'
            ]
        ]);

        $this->payload = [
            'status' => $this->subscription->status,
            'latest_invoice' => [
                'payment_intent' => [
                    'status' => $invoice['payment_intent']['status'],
                    'client_secret' => $invoice['payment_intent']['client_secret']
                ]
            ]
        ];

        return new PrivateChannel('sharingUser.' . $this->subscription->sharingUser->id);
    }
}
