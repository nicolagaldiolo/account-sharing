<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function transactiontable()
    {
        return $this->morphTo();
    }
}
