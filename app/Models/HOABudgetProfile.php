<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class HOABudgetProfile extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'WEG_HGA_PROFIL';
    protected $primaryKey = 'ID';
    protected $searchableFields = ['BEZEICHNUNG', 'JAHR'];
    protected $defaultOrder = ['JAHR' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
