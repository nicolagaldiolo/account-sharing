<?php

namespace App;

use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SharingUser extends Pivot
{

    use Statable;

    protected $guarded = [];

    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    public function sharing(){
        return $this->belongsTo(Sharing::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'sharing_user_id', 'id');
    }


    /*public function renewals()
    {
        return $this->hasMany(Renewal::class, 'sharing_user_id');
    }
    */
}
