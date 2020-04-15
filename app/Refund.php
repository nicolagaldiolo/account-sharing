<?php

namespace App;

use App\Enums\RefundApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{

    protected $fillable = [
        'stripe_id',
        'internal_status',
        'status',
        'reason'
    ];

    protected $with = [
        //'invoice'
    ];

    public function getServiceAttribute()
    {
        return $this->invoice->service;
    }

    public function getTotalAttribute()
    {
        return $this->invoice->total;
    }

    public function getTotalLessFeeAttribute()
    {
        return $this->invoice->total_less_fee;
    }

    public function getCurrencyAttribute()
    {
        return $this->invoice->currency;
    }

    public function getLast4Attribute()
    {
        return $this->invoice->last4;
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
        return $this->hasOneThrough('App\User', 'App\Invoice', 'payment_intent', 'id', 'payment_intent', 'user_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'payment_intent', 'payment_intent');
    }

    public function scopeApproved($query)
    {
        return $query->where('internal_status', RefundApplicationStatus::Approved);
    }

    public function scopePending($query)
    {
        return $query->where('internal_status', RefundApplicationStatus::Pending);
    }
}
