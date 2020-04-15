<?php

namespace App\Observers;

use App\Events\RefundRequest;
use App\Events\RefundResponse;
use App\Refund;

class RefundObserver
{
    /**
     * Handle the refund "created" event.
     *
     * @param  \App\Refund  $refund
     * @return void
     */
    public function created(Refund $refund)
    {
        $refund->transactions()->create();

        event(new RefundRequest($refund));
    }

    /**
     * Handle the refund "updated" event.
     *
     * @param  \App\Refund  $refund
     * @return void
     */
    public function updated(Refund $refund)
    {
        event(new RefundResponse($refund));
    }

    /**
     * Handle the refund "deleted" event.
     *
     * @param  \App\Refund  $refund
     * @return void
     */
    public function deleted(Refund $refund)
    {
        $refund->transactions()->delete();

        event(new RefundResponse($refund));
    }

    /**
     * Handle the refund "restored" event.
     *
     * @param  \App\Refund  $refund
     * @return void
     */
    public function restored(Refund $refund)
    {
        //
    }

    /**
     * Handle the refund "force deleted" event.
     *
     * @param  \App\Refund  $refund
     * @return void
     */
    public function forceDeleted(Refund $refund)
    {
        //
    }
}
