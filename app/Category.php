<?php

namespace App;

use App\Http\Traits\UtilityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use SoftDeletes, UtilityTrait;

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

        // NON POSSO PERCHé CI SONO CASI IN CUI l'UTENTE NON C'è, quando si crea un job ad esempio (trovare alternativa)
        //static::addGlobalScope('country', function ($builder) {
        //    $builder->where('country', Auth::user()->country);
        //});
    }

    public function getSlotAttribute(){
        return $this->getFreeSlot($this);
    }

    public function sharings()
    {
        return $this->hasMany(Sharing::class);
    }

    public function categoryForbidden()
    {
        return $this->hasOne(Sharing::class)->where('owner_id', Auth::user()->id);
    }
}
