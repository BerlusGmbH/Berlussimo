<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\HasEnum;
use App\Models\Traits\Searchable;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use Searchable;
    use DefaultOrder;
    use HasEnum;

    const PARTIALLY_FORWARDED = 'partial';
    const COMPLETELY_FORWARDED = 'complete';
    const NOT_FORWARDED = 'none';
    const CALCULATE_FORWARDED = 'auto';

    public $timestamps = false;
    protected $table = 'RECHNUNGEN';
    protected $primaryKey = 'RECHNUNG_DAT';
    protected $externalKey = 'BELEG_NR';
    protected $searchableFields = ['BELEG_NR', 'RECHNUNGSNUMMER', 'KURZBESCHREIBUNG'];
    protected $defaultOrder = ['RECHNUNGSDATUM' => 'desc'];
    protected $fillable = ['NETTO', 'BRUTTO', 'SKONTOBETRAG'];

    public function updateSums()
    {
        if ($this->BELEG_NR) {
            $belegNr = $this->BELEG_NR;
        } else {
            return;
        }
        $sums = InvoiceLine::where('BELEG_NR', $belegNr)
            ->selectRaw('SUM(GESAMT_NETTO  * ( (100 + MWST_SATZ) /100 ) * ((100-SKONTO)/100 )) AS cashback,
            SUM(GESAMT_NETTO  * ( (100 + MWST_SATZ) /100 )) AS gross,
            SUM(GESAMT_NETTO) AS net')->first();
        $this->update([
            'NETTO' => $sums->net,
            'BRUTTO' => $sums->gross,
            'SKONTOBETRAG' => $sums->cashback
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function scopeOrderByInvoiceDate($query)
    {
        return $query->orderBy('RECHNUNGSDATUM', 'asc');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Bankkonten::class, 'EMPFANGS_GELD_KONTO', 'KONTO_ID');
    }

    public function to(): MorphTo
    {
        return $this->morphTo('to', 'EMPFAENGER_TYP', 'EMPFAENGER_ID', 'id');
    }

    public function from(): MorphTo
    {
        return $this->morphTo('from', 'AUSSTELLER_TYP', 'AUSSTELLER_ID', 'id');
    }

    public function advancePaymentInvoices(): HasMany
    {
        return $this->hasMany(static::class, 'advance_payment_invoice_id', 'advance_payment_invoice_id')
            ->whereIn('RECHNUNGSTYP', ['Teilrechnung', 'Schlussrechnung']);
    }

    public function finalAdvancePaymentInvoice(): HasOne
    {
        return $this->hasOne(static::class, 'advance_payment_invoice_id', 'advance_payment_invoice_id')
            ->where('RECHNUNGSTYP', 'Schlussrechnung');
    }

    public function firstAdvancePaymentInvoice(): BelongsTo
    {
        return $this->belongsTo(static::class, 'advance_payment_invoice_id', 'BELEG_NR');
    }

    public function linesWithProductInformation(): HasMany
    {
        $itemDescriptions = 'POSITIONEN_KATALOG';
        $table = (new InvoiceLine())->getTable();
        return $this->lines()
            ->leftJoin($itemDescriptions, function ($join) use ($itemDescriptions, $table) {
                $join->on($itemDescriptions . '.ART_LIEFERANT', '=', $table . '.ART_LIEFERANT');
                $join->on($itemDescriptions . '.ARTIKEL_NR', '=', $table . '.ARTIKEL_NR');
                $join->where($itemDescriptions . '.AKTUELL', '=', '1');
                $join->where($table . '.AKTUELL', '=', '1');
                $join->whereIn($itemDescriptions . '.KATALOG_ID', function ($query) use ($itemDescriptions) {
                    $query->select(DB::raw('MAX(KATALOG_ID)'))
                        ->from($itemDescriptions)
                        ->groupBy('ART_LIEFERANT', 'ARTIKEL_NR');
                });
            })->select($table . '.*', $itemDescriptions . '.BEZEICHNUNG', $itemDescriptions . '.EINHEIT');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class, 'BELEG_NR', 'BELEG_NR');
    }

    public function forwardedLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class, 'U_BELEG_NR', 'BELEG_NR')
            ->where('BELEG_NR', '<>', DB::raw('U_BELEG_NR'));
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(InvoiceLineAssignment::class, 'BELEG_NR', 'BELEG_NR');
    }

    public function forwarded()
    {
        if ($this->forwarded !== Invoice::CALCULATE_FORWARDED) {
            return $this->forwarded;
        }
        $assignments = $this->assignments;
        $forwardedLines = $this->forwardedLines;
        $state = Invoice::NOT_FORWARDED;
        foreach ($this->lines as $line) {
            $a = $assignments->where('POSITION', $line->POSITION)
                ->where('WEITER_VERWENDEN', '1');
            if ($a->isEmpty()) {
                $forwardedLinesByProduct = $forwardedLines->where('ARTIKEL_NR', $line->ARTIKEL_NR)
                    ->where('ART_LIEFERANT', $line->ART_LIEFERANT);
                if (!$forwardedLinesByProduct->isEmpty()) {
                    $amount = $forwardedLinesByProduct->sum('MENGE');
                }
                if ($amount > 0) {
                    $state = Invoice::PARTIALLY_FORWARDED;
                }
                if ($amount < $line->MENGE) {
                    return $state;
                }
            } else {
                $state = Invoice::PARTIALLY_FORWARDED;
            }
        }
        return $state === Invoice::PARTIALLY_FORWARDED ? Invoice::COMPLETELY_FORWARDED : Invoice::NOT_FORWARDED;
    }
}

