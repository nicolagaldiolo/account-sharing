<?php

namespace App;

use App\Enums\SharingStatus;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'birthday',
        'email',
        'password',
        'pl_account_id',
        'pl_customer_id',
        'phone',
        'street',
        'cap',
        'city',
        'country',
        'currency',
        'tos_acceptance_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'tos_acceptance_at' => 'datetime',
        'address' => 'array',
    ];

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
    }

    public function getStreetAttribute()
    {
        return data_get($this->address, 'street');
    }

    public function setStreetAttribute($value)
    {
        $this->setJsonData('address', 'street', $value);
    }

    public function getCapAttribute()
    {
        return data_get($this->address, 'cap');
    }

    public function setCapAttribute($value)
    {
        $this->setJsonData('address', 'cap', $value);
    }

    public function getCityAttribute()
    {
        return data_get($this->address, 'city');
    }

    public function setCityAttribute($value)
    {
        $this->setJsonData('address', 'city', $value);
    }

    public function setCountryAttribute($value)
    {
        $this->attributes['country'] = $value;
        $this->attributes['currency'] = collect(config('custom.countries'))->get('gb')['currency'] ??
            config('custom.stripe.default_currency');
    }


    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getUsernameAttribute()
    {
        return $this->name . (!empty($this->surname) ? ' ' . $this->surname : '');
    }

    public function getRegistrationCompletedAttribute()
    {
        return !empty($this->birthday) && !empty($this->country) && !empty($this->currency);
    }

    public function getAdditionalDataNeededAttribute()
    {
        return empty($this->phone) || empty($this->street) || empty($this->cap) || empty($this->city);
    }

    protected function setJsonData($item, $key, $value)
    {
        $options = json_decode($this->attributes[$item]);
        $data = data_set($options, $key, $value);
        $this->attributes[$item] = json_encode($data);
    }

    public function sharings(){
        return $this->belongsToMany(Sharing::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_status'])
            ->withTimestamps();
    }
    public function activeSharing(){
        return $this->belongsToMany(Sharing::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','credential_status'])
            ->withTimestamps()
            ->whereStatus(SharingStatus::Joined);
    }
    public function sharingOwners(){
        return $this->hasMany(Sharing::class, 'owner_id', 'id');
    }

    public function sharingUser(Sharing $sharing)
    {
        return $this->hasOne(SharingUser::class)->where('sharing_id', $sharing->id);
    }

    public function subscriptions(){
        return $this->hasManyThrough(Subscription::class, SharingUser::class, 'user_id', 'sharing_user_id');
    }

    public function customers()
    {
        return $this->hasMany(ConnectCustomer::class, 'user_pl_customer_id', 'id');
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'account_id', 'pl_account_id');
    }
}
