<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'RECHNUNGEN_POSITIONEN';
    protected $primaryKey = 'RECHNUNGEN_POS_DAT';
    protected $externalKey = 'RECHNUNGEN_POS_ID';
    protected $defaultOrder = ['POSITION' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'BELEG_NR', 'BELEG_NR');
    }

    public function originatingInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'U_BELEG_NR', 'BELEG_NR');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'ART_LIEFERANT', 'id');
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

    public function getBEZEICHNUNGAttribute()
    {
        if (isset($this->attributes['BEZEICHNUNG'])) {
            return $this->attributes['BEZEICHNUNG'];
        }
        $item = InvoiceItem::where('ART_LIEFERANT', $this->ART_LIEFERANT)
            ->where('ARTIKEL_NR', $this->ARTIKEL_NR)
            ->orderBy('KATALOG_ID', 'desc')
            ->first();
        if ($item) {
            return $item->BEZEICHNUNG;
        }
        return "";
    }

    public function getEINHEITAttribute()
    {
        if (isset($this->attributes['EINHEIT'])) {
            return $this->attributes['EINHEIT'];
        }
        $item = InvoiceItem::where('ART_LIEFERANT', $this->ART_LIEFERANT)
            ->where('ARTIKEL_NR', $this->ARTIKEL_NR)
            ->orderBy('KATALOG_ID', 'desc')
            ->first();
        if ($item) {
            return $item->EINHEIT;
        }
        return "";
    }
}

