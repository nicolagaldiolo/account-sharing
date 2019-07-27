<?php

namespace App;

use App\Enums\RenewalFrequencies;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Model;

class RenewalFrequency extends Model
{

    /*
    use CastsEnums;

    protected $enumCasts = [
        'frequency' => RenewalFrequencies::class
    ];
    */

    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }

    protected $appends = ['frequency'];

    public function getFrequencyAttribute()
    {
        return $this->value . " " . \App\Enums\RenewalFrequencies::getDescription($this->type);
    }

}
