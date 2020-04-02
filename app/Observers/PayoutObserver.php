<?php

namespace App\Observers;

use App\Payout;

class PayoutObserver
{
    /**
     * Handle the payout "created" event.
     *
     * @param  \App\Payout  $payout
     * @return void
     */
    public function created(Payout $payout)
    {
        $payout->transactions()->create();
    }

    /**
     * Handle the payout "updated" event.
     *
     * @param  \App\Payout  $payout
     * @return void
     */
    public function updated(Payout $payout)
    {
        //
    }

    /**
     * Handle the payout "deleted" event.
     *
     * @param  \App\Payout  $payout
     * @return void
     */
    public function deleted(Payout $payout)
    {
        $payout->transactions()->delete();
    }

    /**
     * Handle the payout "restored" event.
     *
     * @param  \App\Payout  $payout
     * @return void
     */
    public function restored(Payout $payout)
    {
        //
    }

    /**
     * Handle the payout "force deleted" event.
     *
     * @param  \App\Payout  $payout
     * @return void
     */
    public function forceDeleted(Payout $payout)
    {
        //
    }
}
