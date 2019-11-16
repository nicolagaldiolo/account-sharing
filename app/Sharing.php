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

    protected $guarded = [];
    protected $appends = [
        'availability',
        'visility_list',
        'owner',
        'sharing_state_machine'
    ];

    protected $toevaluate = [
        'username',
        'password'
    ];

    public function getUsernameAttribute(){
        return Crypt::decryptString($this->attributes['username']);
    }

    public function setUsernameAttribute($value){
        $this->attributes['username'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute(){
        return Crypt::decryptString($this->attributes['password']);
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = Crypt::encryptString($value);
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
            ->withPivot(['id','status','owner','credential_updated_at'])
            ->withTimestamps();
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function activeUsers(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','owner','credential_updated_at'])
            ->whereStatus(SharingStatus::Joined)
            ->withTimestamps();
    }

    public function activeUsersWithoutOwner(){
        return $this->belongsToMany(User::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','owner','credential_updated_at'])
            ->whereStatus(SharingStatus::Joined)
            ->whereOwner(null)
            ->withTimestamps();
    }

    public function getOwnerAttribute()
    {
        return $this->activeUsers()->whereOwner(true)->first();
    }

    public function getSharingStateMachineAttribute()
    {
        $obj = $this->users()->find(Auth::id());

        if (!is_null($obj)) {
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

    public function renewalFrequency(){
        return $this->belongsTo(RenewalFrequency::class);
    }

    public function getVisilityListAttribute()
    {
        return SharingVisibility::toSelectArray();
    }

    /*public function calcNextRenewal($current = null)
    {
        $renewalFrequency = $this->renewalFrequency;
        $date = ($current instanceof Carbon) ? $current : Carbon::now();
        $expires_on = null;

        switch ($renewalFrequency->type){
            case RenewalFrequencies::Weeks:
                $expires_on = $date->addWeekNoOverflow($renewalFrequency->value)->endOfDay();
                break;
            case RenewalFrequencies::Months:
                $expires_on = $date->addMonthNoOverflow($renewalFrequency->value)->endOfDay();
                break;
            case RenewalFrequencies::Years:
                $expires_on = $date->addYearNoOverflow($renewalFrequency->value)->endOfDay();
                break;
        }

        return $expires_on;
    }
    */

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
