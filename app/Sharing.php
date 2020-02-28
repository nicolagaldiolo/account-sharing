<?php

namespace App;

use App\Enums\SharingStatus;
use App\Enums\SharingVisibility;
use App\Http\Traits\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sharing extends Model
{
    use SoftDeletes, Utility;

    protected $fillable = [
        'name',
        'description',
        'visibility',
        'status',
        'slot',
        'price',
        'multiaccount',
        'stripe_plan',
        'image',
        'renewal_frequency_id',
        'category_id'
    ];

    public function setSlotAttribute($value){
        $this->attributes['slot'] = $value;
        $this->attributes['capacity'] = $this->calcCapacity($value);
    }

    public function getAvailabilityAttribute(){
        return $this->slot - $this->members()->count();
    }

    public function getPriceNoFeeAttribute(){
        return $this->price / $this->capacity;
    }

    public function getPriceWithFeeAttribute(){
        return ($this->price / $this->capacity) + (floatval(config('custom.stripe.stripe_fee')) / 100) + (floatval(config('custom.stripe.platform_fee')) / 100);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_status'])
            ->withTimestamps();
    }

    public function approvedUsers(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_status'])
            ->whereStatus(SharingStatus::Approved)
            ->withTimestamps();
    }

    public function members(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_status'])
            ->whereStatus(SharingStatus::Joined)
            ->withTimestamps();
    }

    public function credentials(User $user = null){

        if($this->multiaccount){
            $relation = $this->hasManyThrough(Credential::class, SharingUser::class, 'sharing_id', 'credentiable_id')
                ->where('credentiable_type', SharingUser::class)
                ->whereStatus(SharingStatus::Joined)
                ->when(!is_null($user), function($query) use($user){
                    $query->where('sharing_user.user_id', $user->id);
                })->with('credentiable');
        }else{
            $relation = $this->morphMany(Credential::class, 'credentiable');
        }

        return $relation;
    }

    public function getCredentialStatusAttribute()
    {
        return $this->calcCredentialStatus($this);
    }

    public function subscriptions(){
        return $this->hasManyThrough(Subscription::class, SharingUser::class, 'sharing_id', 'sharing_user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function sharingUser(User $user = null)
    {
        $user = empty($user) ? Auth::user() : $user;

        return $this->hasOne(SharingUser::class)->where('user_id', $user->id);
    }

    public function renewalFrequency(){
        return $this->belongsTo(RenewalFrequency::class);
    }

    public function getVisilityListAttribute()
    {
        return SharingVisibility::toSelectArray();
    }

    public function scopePending($query)
    {
        return $query->where('sharing_user.status', SharingStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('sharing_user.status', SharingStatus::Approved);
    }

    public function scopePublic($query)
    {
        return $query->withCount('members')->havingRaw('`members_count` < `sharings`.`slot`')->whereVisibility(SharingVisibility::Public);
        //return $query->whereVisibility(SharingVisibility::Public);
    }

    public function scopeJoined($query)
    {
        return $query->where('sharing_user.status', SharingStatus::Joined);
    }

/*$posts = App\Post::withCount(['votes', 'comments' => function (Builder $query) {
    $query->where('content', 'like', 'foo%');
}])->get();*/

}
