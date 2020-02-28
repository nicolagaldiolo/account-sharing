<?php

namespace App;

use App\Http\Traits\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use SoftDeletes, Utility;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'price',
        'image',
        'custom',
        'multiaccount',
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

    public function getSlotAttribute(){
        return $this->getFreeSlot($this);
    }

    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }

    public function categoryForbidden()
    // I don't create a sharing if i already created one in this category
    {
        return $this->hasOne(Sharing::class)->where('owner_id', Auth::user()->id);
    }
}
