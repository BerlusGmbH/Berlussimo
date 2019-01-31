<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailCategory extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'DETAIL_KATEGORIEN';
    protected $primaryKey = 'DETAIL_KAT_ID';
    protected $defaultOrder = ['DETAIL_KAT_KATEGORIE' => 'asc', 'DETAIL_KAT_NAME' => 'asc'];
    protected $fillable = ['DETAIL_KAT_KATEGORIE', 'DETAIL_KAT_NAME', 'DETAIL_KAT_AKTUELL'];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('DETAIL_KAT_AKTUELL', '1');
        });
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(DetailSubcategory::class, 'KATEGORIE_ID', 'DETAIL_KAT_ID');
    }
}
