<?php

namespace App;

use App\Enums\RenewalFrequencies;
use App\Enums\SharingStatus;
use App\Enums\SharingVisibility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sharing extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $appends = ['availability', 'visility_list'];

    /*
     * Scopes
     */

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('onwer', function (Builder $builder){
            $builder->with('owner');
        });
    }

    public function getAvailabilityAttribute(){
        return $this->capacity - $this->activeUsers()->count();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['status','id'])
            ->withTimestamps();
    }

    public function activeUsers(){
        return $this->belongsToMany(User::class)
            ->whereStatus(SharingStatus::Joined);
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function renewalFrequency(){
        return $this->belongsTo(RenewalFrequency::class);
    }

    public function getVisilityListAttribute()
    {
        return SharingVisibility::toSelectArray();
    }

    public function calcNextRenewal($current = null)
    {
        $renewalFrequency = $this->renewalFrequency;
        $date = ($current instanceof Carbon) ? $current : Carbon::now();
        $expires_on = null;

        switch ($renewalFrequency->type){
            case RenewalFrequencies::Weeks:
                $expires_on = $date->addWeek($renewalFrequency->value)->endOfDay();
                break;
            case RenewalFrequencies::Months:
                $expires_on = $date->addMonth($renewalFrequency->value)->endOfDay();
                break;
            case RenewalFrequencies::Years:
                $expires_on = $date->addYear($renewalFrequency->value)->endOfDay();
                break;
        }

        return $expires_on;
    }

    public function scopePending($query)
    {
        return $query->whereStatus(SharingStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->whereStatus(SharingStatus::Approved);
    }

    public function scopePublic($query)
    {
        return $query->whereVisibility(SharingVisibility::Public);
    }

    public function scopeJoined($query)
    {
        return $query->whereStatus(SharingStatus::Joined);
    }

    // dato uno stato torno tutte le condivisioni in quello stato
    public function scopeByStatus($query, $status = SharingStatus::Pending)
    {
        return $query->whereHas('users', function($query) use($status){
            $query->where('status',$status);
        });
    }
}
