<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'stripe_id',
        'status',
        'cancel_at_period_end',
        'ended_at',
        'current_period_end_at'
    ];

    protected $casts = [
        'ended_at' => 'datetime',
        'current_period_end_at' => 'datetime'
    ];

    function sharingUser()
    {
        return $this->belongsTo(SharingUser::class);
    }
}
