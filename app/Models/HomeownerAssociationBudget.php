<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class HomeownerAssociationBudget extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'WEG_WPLAN';
    protected $primaryKey = 'PLAN_ID';
    protected $defaultOrder = ['JAHR' => 'desc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'homeowner_association_budget';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
