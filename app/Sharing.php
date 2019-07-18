<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sharing extends Model
{
    use SoftDeletes;

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function owner(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
