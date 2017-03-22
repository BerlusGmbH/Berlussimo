<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AktuellScope;

class Bankkonten extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'GELD_KONTEN';
    protected $primaryKey = 'KONTO_ID';
    protected $searchableFields = ['BEZEICHNUNG', 'BEGUENSTIGTER', 'IBAN', 'BIC', 'INSTITUT'];
    protected $defaultOrder = ['BEZEICHNUNG' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function objekte() {
        return $this->belongsToMany(Objekte::class, 'GELD_KONTEN_ZUWEISUNG', 'KONTO_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'OBJEKT')->wherePivot('AKTUELL', '1');
    }

    public function partner() {
        return $this->belongsToMany(Objekte::class, 'GELD_KONTEN_ZUWEISUNG', 'KONTO_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'PARTNER')->wherePivot('AKTUELL', '1');
    }
}
