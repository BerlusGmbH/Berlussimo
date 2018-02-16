<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Wirtschaftseinheiten extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'WIRT_EINHEITEN';
    protected $primaryKey = 'W_ID';
    protected $searchableFields = ['W_NAME'];
    protected $defaultOrder = ['W_NAME' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function einheiten()
    {
        return $this->belongsToMany('App\Models\Einheiten', 'WIRT_EIN_TAB', 'W_ID', 'EINHEIT_ID')->wherePivot('AKTUELL', '1');
    }
}
