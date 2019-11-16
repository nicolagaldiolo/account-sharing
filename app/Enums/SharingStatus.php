<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class SharingStatus extends Enum
{
    const Pending   = 0;
    const Approved  = 1;
    const Refused   = 2;
    const Joined    = 3;
    const Left      = 4;
}
