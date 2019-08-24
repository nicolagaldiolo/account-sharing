<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Renewal extends Model
{

    protected $fillable = ['status', 'expire_on'];

    protected $dates = ['expire_on'];

    public function sharingUser(){
        return $this->belongsTo(SharingUser::class, 'id');
    }
}
