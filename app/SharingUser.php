<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SharingUser extends Pivot
{

    protected $guarded = [];

    public function sharing(){
        return $this->belongsTo(Sharing::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'sharing_user_id');
    }
}
