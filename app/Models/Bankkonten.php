<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Bankkonten extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'GELD_KONTEN';
    protected $primaryKey = 'KONTO_ID';
    protected $searchableFields = ['BEZEICHNUNG', 'IBAN', 'BIC', 'KONTONUMMER', 'BLZ'];
    protected $defaultOrder = ['BEZEICHNUNG' => 'asc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'bank_account';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function objekte() {
        return $this->belongsToMany(Objekte::class, 'GELD_KONTEN_ZUWEISUNG', 'KONTO_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'Objekt')->wherePivot('AKTUELL', '1');
    }

    public function partner() {
        return $this->belongsToMany(Partner::class, 'GELD_KONTEN_ZUWEISUNG', 'KONTO_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'Partner')->wherePivot('AKTUELL', '1');
    }

    public function purchaseContracts()
    {
        return $this->belongsToMany(Kaufvertraege::class, 'GELD_KONTEN_ZUWEISUNG', 'KONTO_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'Eigentuemer')->wherePivot('AKTUELL', '1');
    }

    public function details()
    {
        return $this->morphMany(Details::class, 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function getChunkedIBANAttribute()
    {
        return trim(chunk_split($this->IBAN, 4, ' '));
    }
}
