<?php

namespace App;

use App\Enums\RenewalFrequencies;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Model;

class RenewalFrequency extends Model
{

    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }

}
