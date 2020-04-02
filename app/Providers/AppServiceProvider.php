<?php

namespace App\Providers;

use App\Http\Requests\SharingRequest;
use App\Invoice;
use App\Observers\InvoiceObserver;
use App\Observers\PayoutObserver;
use App\Observers\RefundObserver;
use App\Observers\SharingObserver;
use App\Observers\UserObserver;
use App\Payout;
use App\Refund;
use App\Sharing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;
use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningUnitTests()) {
            Schema::defaultStringLength(191);
        }

        User::observe(UserObserver::class);
        Sharing::observe(SharingObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Refund::observe(RefundObserver::class);
        Payout::observe(PayoutObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");
    }
}
