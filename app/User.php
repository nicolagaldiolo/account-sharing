<?php

namespace App;

use App\Enums\SharingStatus;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
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
        'name','surname','birthday','email','password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'photo_url',
        'username',
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

    public function sharings(){
        return $this->belongsToMany(Sharing::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','owner','credential_updated_at'])
            ->withTimestamps();
    }
    public function activeSharing(){
        return $this->belongsToMany(Sharing::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','owner','credential_updated_at'])
            ->withTimestamps()
            ->whereStatus(SharingStatus::Joined)
            ->whereNull('owner');
    }
    public function sharingOwners(){
        return $this->belongsToMany(Sharing::class)
            ->using(SharingUser::class)
            ->as('sharing_status')
            ->withPivot(['id','status','owner'])
            ->withTimestamps()
            ->whereStatus(SharingStatus::Joined)
            ->whereOwner(true);
    }


    public function customers()
    {
        return $this->hasMany(ConnectCustomer::class, 'user_pl_customer_id', 'id');
    }

    //public function customersAsOwner()
    //{
    //    return $this->hasMany(ConnectCustomer::class, 'user_pl_account_id', 'id');
    //}

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'account_id', 'pl_account_id');
    }
}
