<?php

namespace App;

use App\Enums\SharingStatus;
use App\Enums\SharingVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sharing extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $appends = ['availability', 'visility_list'];

    public function getAvailabilityAttribute(){
        return $this->capacity - $this->activeUsers()->count();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('status');
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
    //public function scopePublicAvailable($query){
    //    logger($this->availability);
    //    return $query
    //        ->where('visibility', SharingVisibility::Public),
    //        ->where()
    //}


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

    // dato uno stato totno tutte le connessioni in quello stato
    public function scopeByStatus($query, $status = SharingStatus::Pending)
    {
        return $query->whereHas('users', function($query) use($status){
            $query->where('status',$status);
        });
    }
}
