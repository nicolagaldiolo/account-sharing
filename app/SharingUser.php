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
        'looked_at' => 'datetime'
    ];

    protected function getGraph()
    {
        return 'sharing';
    }

    public function sharing(){
        return $this->belongsTo(Sharing::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'sharing_user_id', 'id');
    }

    public function credentials()
    {
        return $this->morphOne(Credential::class, 'credentiable');
    }
}
