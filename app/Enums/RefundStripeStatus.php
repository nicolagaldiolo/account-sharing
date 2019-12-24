<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RefundStripeStatus extends Enum
{
    const pending =   0;
    const succeeded =   1;
    const failed = 2;
}
