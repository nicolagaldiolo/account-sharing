<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CredentialsStatus extends Enum
{
    const Toverify =   0;
    const Confirmed =   1;
    const Wrong = 2;
}
