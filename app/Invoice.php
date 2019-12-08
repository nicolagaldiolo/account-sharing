<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $fillable = [
        'stripe_id',
        'customer_id',
        'account_id',
        'subscription_id',
        'payment_intent',
        'total',
        'currency',
        'last4'
    ];

    protected $with = [
        'subscription.sharingUser.sharing',
        'user'
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
        return $this->belongsTo(Subscription::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_intent', 'payment_intent');
    }
}
