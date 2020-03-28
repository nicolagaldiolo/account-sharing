<?php

namespace App;

use App\Enums\RefundApplicationStatus;
use App\Http\Traits\Utility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use Utility;

    protected $fillable = [
        'stripe_id',
        'customer_id',
        'user_id',
        'subscription_id',
        'payment_intent',
        'service_name',
        'total',
        'total_less_fee',
        'fee',
        'currency',
        'last4',
        'transfered'
    ];

    public function getServiceAttribute()
    {
        return $this->attributes['service_name'];
    }

    public function getLast4Attribute()
    {
        return $this->attributes['last4'];
    }

    public function getTotalAttribute()
    {
        return $this->attributes['total'];
    }

    public function transactions()
    {
        return $this->morphMany('App\Transaction', 'transactiontable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'pl_customer_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subscription()
    {
        return $this->belongsTo( Subscription::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'payment_intent', 'payment_intent');
    }

    public function refundApproved()
    {
        return $this->hasOne(Refund::class, 'payment_intent', 'payment_intent')->approved();
    }

    //public function transfer()
    //{
    //    return $this->hasOne(Transfer::class);
    //}

    /*
     * Una fattura è trasferibile quando:
     * 1 sono passati 25 giorni dalla data di emissione
     * 2 non è già stata trasferita
     * 3 non ha un rimborso approvato
     */

    public function scopeTransferable($query)
    {
        /*return $query->doesntHave('transfer')->whereDoesntHave('refunds', function ($query) {
            $query->approved();
        })->where('created_at', '<=', Carbon::now()->subDays(config('custom.day_refund_limit'))->endOfDay());*/

        return $query->whereDoesntHave('refund', function ($query) {
            $query->approved();
        })->where('transfered',0)
            ->where('created_at', '<=', Carbon::now()->subDays(config('custom.day_refund_limit'))->endOfDay());

    }
}
