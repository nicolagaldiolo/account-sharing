<?php

namespace App;

use App\Http\Traits\UtilityTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use UtilityTrait;

    protected $fillable = [
        'stripe_id',
        'customer_id',
        'account_id',
        'subscription_id',
        'payment_intent',
        'total',
        'total_less_fee',
        'currency',
        'last4'
    ];

    protected $with = [
        //'subscription.sharingUser.sharing',
        //'user',
        //'owner'
    ];

    public function getServiceAttribute()
    {
        return $this->subscription->sharingUser->sharing->name;
    }

    public function getLast4Attribute()
    {
        return $this->attributes['last4'];
    }

    public function getTotalAttribute()
    {
        return $this->attributes['total'];
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = $value;
        $this->attributes['total_less_fee'] = $this->calcNetPrice($value);
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
        return $this->belongsTo(User::class, 'account_id', 'pl_account_id');
    }

    public function subscription()
    {
        return $this->belongsTo( Subscription::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_intent', 'payment_intent');
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class);
    }

    /*
     * Una fattura è trasferibile quando:
     * 1 sono passati 25 giorni dalla data di emissione
     * 2 non è già stata trasferita
     * 3 non ha un rimborso approvato
     */

    public function scopeTransferable($query)
    {
        return $query->doesntHave('transfer')->whereDoesntHave('refunds', function ($query) {
            $query->approved();
        })->where('created_at', '<=', Carbon::now()->subDays(config('custom.day_refund_limit'))->endOfDay());
    }
}
