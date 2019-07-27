<?php

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

final class RenewalFrequencies extends Enum implements LocalizedEnum
{
    const Weeks = 0;
    const Months = 1;
    const Years = 2;
}
