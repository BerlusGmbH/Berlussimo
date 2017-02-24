<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AktuellScope;

class BaustellenExtern extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'BAUSTELLEN_EXT';
    protected $primaryKey = 'DAT';
    protected $searchableFields = ['BEZ'];
    protected $defaultOrder = ['AKTIV' => 'asc', 'BEZ' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function kostentraeger()
    {
        return $this->morphTo('kostentraeger', 'KOSTENTRAEGER_TYP', 'KOSTENTRAEGER_ID');
    }
}
