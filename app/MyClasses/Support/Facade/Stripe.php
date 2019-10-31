<?php

namespace App\MyClasses\Support\Facade;

use Illuminate\Support\Facades\Facade;

class Stripe extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\MyClasses\Stripe::class;
    }
}
