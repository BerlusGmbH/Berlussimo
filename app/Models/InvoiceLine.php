<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'RECHNUNGEN_POSITIONEN';
    protected $primaryKey = 'RECHNUNGEN_POS_ID';
    protected $defaultOrder = ['POSITION' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'BELEG_NR');
    }

    public function assignedAmount()
    {
        return $this->assignments()->sum('MENGE');
    }

    public function assignments()
    {
        return InvoiceLineAssignment::where('BELEG_NR', $this->BELEG_NR)
            ->where('POSITION', $this->POSITION);
    }
}

