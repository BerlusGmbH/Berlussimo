<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class DetailSubcategory extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'DETAIL_UNTERKATEGORIEN';
    protected $primaryKey = 'UKAT_DAT';
    protected $defaultOrder = ['KATEGORIE_ID' => 'asc', 'UNTERKATEGORIE_NAME' => 'asc'];
    protected $fillable = ['KATEGORIE_ID', 'UNTERKATEGORIE_NAME', 'AKTUELL'];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function category()
    {
        return $this->belongsTo(DetailCategory::class, 'KATEGORIE_ID');
    }
}
