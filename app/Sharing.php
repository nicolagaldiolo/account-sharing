<?php

namespace App;

use App\Enums\SharingStatus;
use App\Enums\SharingVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sharing extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $appends = ['availability'];

    public function getAvailabilityAttribute(){
        return $this->capacity - $this->activeUsers()->count();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function activeUsers(){
        return $this->belongsToMany(User::class)
            ->whereStatus(SharingStatus::Joined);
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    //public function scopePublicAvailable($query){
    //    logger($this->availability);
    //    return $query
    //        ->where('visibility', SharingVisibility::Public),
    //        ->where()
    //}
}
