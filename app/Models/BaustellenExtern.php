<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class BaustellenExtern extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'BAUSTELLEN_EXT';
    protected $primaryKey = 'DAT';
    protected $searchableFields = ['BEZ'];
    protected $defaultOrder = ['AKTIV' => 'asc', 'BEZ' => 'asc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'construction_site';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
