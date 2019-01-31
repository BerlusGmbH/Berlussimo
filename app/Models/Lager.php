<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Lager extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'LAGER';
    protected $primaryKey = 'LAGER_DAT';
    protected $externalKey = 'LAGER_ID';
    protected $searchableFields = ['LAGER_NAME', 'LAGER_VERWALTER'];
    protected $defaultOrder = ['LAGER_NAME' => 'asc', 'LAGER_VERWALTER' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID', 'LAGER_ID');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['']);
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'LAGER_ID');
    }
}
