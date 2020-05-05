<?php

namespace App;

use App\Http\Traits\Utility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
    //protected static function boot()
    //{
    //    parent::boot();

    //    static::addGlobalScope('country', function ($builder) {
    //        $builder->where('country', Auth::user()->country);
    //    });
    //}

    public function setImageAttribute($image){
        if($image) {
            if (Storage::exists($this->image)) Storage::delete($this->image);
            $this->attributes['image'] = $image->store('uploads/categories');
        }
    }

    public function getPublicImageAttribute(){
        $image = ($this->image && Storage::exists($this->image)) ? $this->image : config('custom.default_image');
        return Storage::url($image);
    }

    public function getFreeSlotAttribute(){
        return $this->capacity -1;
    }

    public function getIsForbiddenAttribute(){
        return $this->sharingForbidden->count() > 0;
    }

    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }

    // I don't create a sharing if i already created one in this category
    // (except custom groups) or i'm joiner in one of them
    public function sharingForbidden()
    {
        return $this->hasMany(Sharing::class)
            ->whereDoesntHave('category', function (Builder $query){
                $query->where('custom', 1);
            })
            ->where(function(Builder $query){
                $query->where('owner_id', Auth::id())
                    ->orWhereHas('members', function (Builder $query){
                        $query->where('user_id', Auth::id());
                    });
            });
    }

    //public function scopeAllowed()
    //{
    //    return $this->whereHas('sharings', function (Builder $query){
    //       $query->where('owner_id', Auth::id());
    //    });
    //}

    public function scopeCountry($query){
        return $query->where('country', Auth::user()->country);
    }
}
