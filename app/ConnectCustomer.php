<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectCustomer extends Model
{
    public function user(){
        return $this->belongsTo(User::class, 'user_pl_customer_id', 'id');
    }
}
