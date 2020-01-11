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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;

class Sharing extends Model
{
    use SoftDeletes;

    // https://stackoverflow.com/questions/36750540/accessing-a-database-value-in-a-models-constructor-in-laravel-5-2
    // Alla crezione del modello condiziono dimamicamente i campi che voglio nascondere previa condizione
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        $user = Auth::user();
        if($user) {
            $permitted = $user->can('manage-sharing', $model);
            if (!$permitted) $model->setHidden($model->toevaluate);
        }

        return $model;
    }

    protected $fillable = [
        'name',
        'description',
        'visibility',
        'capacity',
        'price',
        'stripe_plan',
        'image',
        'renewal_frequency_id',
        'category_id',
        'username',
        'password'
    ];

    protected $with = [
        //'category',
        //'owner'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    //protected static function boot()
    //{
    //    parent::boot();

    //    static::addGlobalScope('country', function ($builder) {
    //        $builder->whereHas('category');
    //    });
    //}

    protected $casts = [
        'credential_updated_at' => 'datetime'
    ];

    protected $appends = [
        //'availability',
        //'visility_list',
        //'owner',
        //'sharing_state_machine'
    ];

    protected $toevaluate = [
        //'username',
        //'password'
    ];

    public function getUsernameAttribute($value){
        return (!empty($value)) ? Crypt::decryptString($value) : $value;
    }

    public function setUsernameAttribute($value){
        $this->attributes['username'] = (!empty($value)) ? Crypt::encryptString($value) : $value;
    }

    public function getPasswordAttribute($value){
        return (!empty($value)) ? Crypt::decryptString($value) : $value;
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = (!empty($value)) ? Crypt::encryptString($value) : $value;
    }


    public function getAvailabilityAttribute(){
        return $this->capacity - $this->members()->count();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_updated_at'])
            ->withTimestamps();
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function approvedUsers(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_updated_at'])
            ->whereStatus(SharingStatus::Approved)
            ->withTimestamps();
    }

    public function members(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_updated_at'])
            ->whereStatus(SharingStatus::Joined)
            ->withTimestamps();
    }

    /*public function activeUsersWithoutOwner(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_updated_at'])
            ->whereStatus(SharingStatus::Joined)
            ->where('user_id', '<>', $this->owner_id)
            ->withTimestamps();
    }*/

    public function subscriptions(){
        return $this->hasManyThrough(Subscription::class, SharingUser::class, 'sharing_id', 'sharing_user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /*public function getSharingStateMachineAttribute()
    {
        $obj = $this->users()->find(Auth::id());

        if (!empty($obj)) {
            $stateMachine = \StateMachine::get($obj->sharing_status, 'sharing');
            return [
                'status' => [
                    'value' => $stateMachine->getState(),
                    'metadata' => $stateMachine->metadata('state'),
                ],
                'transitions' => collect([])->merge(collect($stateMachine->getPossibleTransitions())->map(function ($value) use ($stateMachine) {
                    return [
                        'value' => $value,
                        'metadata' => $stateMachine->metadata()->transition($value)
                    ];
                }))->all(), // altrimenti non mi torna un array;
            ];
        } else {
            return null;
        }
    }
    */

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
}
