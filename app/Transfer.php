<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'stripe_id',
        'account_id',
        'amount',
        'currency'
    ];

    //public function transactions()
    //{
    //    return $this->morphMany('App\Transaction', 'transactiontable');
    //}

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
