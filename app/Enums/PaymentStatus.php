<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentStatus extends Enum
{
    const Pending = 0;
    const Successful = 1;
    const Refused = 2;
}
