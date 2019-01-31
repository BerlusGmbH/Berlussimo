<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class InvoiceLineAssignment extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'KONTIERUNG_POSITIONEN';
    protected $primaryKey = 'KONTIERUNG_DAT';
    protected $externalKey = 'KONTIERUNG_ID';
    protected $defaultOrder = ['POSITION' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function costUnit()
    {
        return $this->morphTo('costUnit', 'KOSTENTRAEGER_TYP', 'KOSTENTRAEGER_ID', 'id');
    }

    public function line()
    {
        return InvoiceLine::where('BELEG_NR', $this->BELEG_NR)
            ->where('POSITION', $this->POSITION)
            ->first();
    }
}

