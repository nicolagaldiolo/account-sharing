<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'stripe_id',
        'payment_intent',
        'amount',
        'currency',
        'last4'
    ];

    protected $with = [
        'invoice'
    ];

    public function getServiceAttribute()
    {
        return $this->invoice->service;
    }

    public function getTotalAttribute()
    {
        return $this->attributes['amount'];
    }

    public function getLast4Attribute()
    {
        return $this->attributes['last4'];
    }

    public function transactions()
    {
        return $this->morphMany('App\Transaction', 'transactiontable');
    }

    public function user()
    {
        return $this->hasOneThrough('App\User', 'App\Invoice', 'payment_intent', 'pl_customer_id', 'payment_intent', 'customer_id');
    }

    public function owner()
    {
        return $this->hasOneThrough('App\User', 'App\Invoice', 'payment_intent', 'pl_account_id', 'payment_intent', 'account_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'payment_intent', 'payment_intent');
    }
}
