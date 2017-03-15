<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AktuellScope;

class Gewerke extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'GEWERKE';
    protected $primaryKey = 'G_ID';
    protected $searchableFields = ['BEZEICHNUNG'];
    protected $defaultOrder = ['BEZEICHNUNG' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
