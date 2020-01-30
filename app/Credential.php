<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Credential extends Model
{
    protected $fillable = [
        'credentiable_id',
        'credentiable_type',
        'username',
        'password',
        'credential_updated_at'
    ];

    protected $casts = [
        'credential_updated_at' => 'datetime'
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

    public function credentiable()
    {
        return $this->morphTo();
    }
}
