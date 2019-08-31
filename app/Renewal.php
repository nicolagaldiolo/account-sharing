<?php

namespace App;

use App\Enums\RenewalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Renewal extends Model
{

    protected $fillable = ['status', 'starts_at', 'expires_at'];

    protected $dates = ['starts_at','expires_at'];

    public function sharingUser()
    {
        return $this->belongsTo(SharingUser::class);
    }

    public function scopeToPay($query)
    {
        return $query
            ->whereStatus(RenewalStatus::Pending)
            ->where('starts_at', '<=', Carbon::now()->startOfDay())
            ->where('expires_at', '>=', Carbon::now()->endOfDay());
    }
}