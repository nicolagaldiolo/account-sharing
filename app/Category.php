<?php

namespace App;

use App\Http\Traits\Utility;
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
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('country', function ($builder) {
            $builder->where('country', Auth::user()->country);
        });
    }

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
