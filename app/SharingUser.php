<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SharingUser extends Pivot
{

    public $incrementing = true;

    protected $guarded = [];

    public function sharing(){
        return $this->belongsTo(Sharing::class);
    }

    public function renewals()
    {
        return $this->hasMany(Renewal::class, 'sharing_user_id');
    }
}
