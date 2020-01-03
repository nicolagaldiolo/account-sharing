<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'price',
        'image',
        'customizable',
        'country'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('country', function ($builder) {
            $builder->where('country', Auth::user()->country);
        });
    }


    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }
}
