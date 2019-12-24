<?php

namespace App\Http\Traits;

trait UtilityTrait
{
    protected function calcNetPrice($price = 0)
    {
        return (intval($price) > 0) ?
            (intval($price) - intval(config('custom.stripe.stripe_fee')) - intval(config('custom.stripe.platform_fee'))) :
            0;
    }
}
