<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RenewalStatus extends Enum
{
    const Pending = 0;
    const Confirmed = 1;
    const Stopped = 2;
}
