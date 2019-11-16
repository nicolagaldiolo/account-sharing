<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class SubscriptionStatus extends Enum
{
    const trialing =   0;
    const active =   1;
    const incomplete = 2;
    const incomplete_expired = 3;
    const past_due = 4;
    const canceled = 5;
    const unpaid = 6;
}
