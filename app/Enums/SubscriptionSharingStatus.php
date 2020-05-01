<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SubscriptionSharingStatus extends Enum
{
    const succeeded = 0;
    const requires_action = 1;
    const requires_payment_method = 2;
}
