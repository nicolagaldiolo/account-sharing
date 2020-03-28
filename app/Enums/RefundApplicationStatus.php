<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RefundApplicationStatus extends Enum
{
    const Pending = 0;
    const Approved = 1;
    const Refused = 2;
}
