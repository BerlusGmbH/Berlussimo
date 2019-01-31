<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class InvoiceItemUnit extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'VERPACKUNGS_E';
    protected $primaryKey = 'V_ID';
    protected $defaultOrder = ['BEZEICHNUNG' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

}

