<?php

namespace App\Observers;

use App\Sharing;
use Illuminate\Support\Facades\Auth;

class SharingObserver
{
    /**
     * Handle the sharing "created" event.
     *
     * @param  \App\Sharing  $sharing
     * @return void
     */
    public function created(Sharing $sharing)
    {
        //
    }

    /**
     * Handle the sharing "updated" event.
     *
     * @param  \App\Sharing  $sharing
     * @return void
     */
    public function updated(Sharing $sharing)
    {
        //
    }

    /**
     * Handle the sharing "deleted" event.
     *
     * @param  \App\Sharing  $sharing
     * @return void
     */
    public function deleted(Sharing $sharing)
    {
        //
    }

    /**
     * Handle the sharing "restored" event.
     *
     * @param  \App\Sharing  $sharing
     * @return void
     */
    public function restored(Sharing $sharing)
    {
        //
    }

    /**
     * Handle the sharing "force deleted" event.
     *
     * @param  \App\Sharing  $sharing
     * @return void
     */
    public function forceDeleted(Sharing $sharing)
    {
        //
    }
}
