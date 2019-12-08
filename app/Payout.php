<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{

    protected $fillable = [
        'stripe_id',
        'amount',
        'currency',
        'ccnumber'
    ];

    protected $with = [
        'owner'
    ];

    public function getServiceAttribute()
    {
        return null;
    }

    public function getLast4Attribute()
    {
        return $this->attributes['ccnumber'];
    }

    public function getTotalAttribute()
    {
        return $this->attributes['amount'];
    }

    public function user()
    {
        return $this->owner();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'account_id', 'pl_account_id');
    }

    public function transactions()
    {
        return $this->morphMany('App\Transaction', 'transactiontable');
    }

}
