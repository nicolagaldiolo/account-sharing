<?php

namespace App;

use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SharingUser extends Pivot
{

    use Statable;

    protected $guarded = [];

    protected $casts = [
        'credential_updated_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    protected function getGraph()
    {
        return 'sharing';
    }

    public function sharing(){
        return $this->belongsTo(Sharing::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'sharing_user_id', 'id');
    }
}
