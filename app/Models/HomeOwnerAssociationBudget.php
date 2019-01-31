<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class HomeOwnerAssociationBudget extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'WEG_WPLAN';
    protected $primaryKey = 'DAT';
    protected $externalKey = 'PLAN_ID';
    protected $defaultOrder = ['JAHR' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
