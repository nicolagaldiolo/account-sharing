<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{

    public $incrementing = false;

    protected $fillable = [
        'id',
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

    public function sharing()
    {
        return $this->hasOneThrough('App\Sharing', 'App\SharingUser');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }


    /*
     * public function subscription()
    {
        return $this->hasOneThrough('App\Subscription', 'App\SharingUser', 'sharing_id', 'sharing_user_id', 'id', 'id');
    }
     */
}
