<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\HasEnum;
use App\Models\Traits\Searchable;
use DB;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use Searchable;
    use DefaultOrder;
    use HasEnum;

    public $timestamps = false;
    protected $table = 'RECHNUNGEN';
    protected $primaryKey = 'BELEG_NR';
    protected $searchableFields = ['RECHNUNGSNUMMER', 'KURZBESCHREIBUNG'];
    protected $defaultOrder = ['RECHNUNGSDATUM' => 'desc'];
    protected $fillable = ['NETTO', 'BRUTTO', 'SKONTOBETRAG'];

    public static function updateSums($invoiceLine)
    {
        if ($invoiceLine instanceof InvoiceLine) {
            $invoiceLine = $invoiceLine->BELEG_NR;
        }
        $sums = InvoiceLine::where('BELEG_NR', $invoiceLine)
            ->selectRaw('SUM(GESAMT_NETTO  * ( (100 + MWST_SATZ) /100 ) * ((100-SKONTO)/100 )) AS cashback,
            SUM(GESAMT_NETTO  * ( (100 + MWST_SATZ) /100 )) AS gross,
            SUM(GESAMT_NETTO) AS net')->first();
        Invoice::find($invoiceLine)->update([
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

    public function interimPool()
    {
        return $this->hasOne(InvoicesInterimPool::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(Bankkonten::class, 'EMPFANGS_GELD_KONTO');
    }

    public function to()
    {
        return $this->morphTo('to', 'EMPFAENGER_TYP', 'EMPFAENGER_ID');
    }

    public function from()
    {
        return $this->morphTo('from', 'AUSSTELLER_TYP', 'AUSSTELLER_ID');
    }

    public function lines()
    {
        $itemDescriptions = 'POSITIONEN_KATALOG';
        $table = (new InvoiceLine())->getTable();
        return $this->hasMany(InvoiceLine::class, 'BELEG_NR', 'BELEG_NR')
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

    public function assignments()
    {
        return $this->hasMany(InvoiceLineAssignment::class, 'BELEG_NR', 'BELEG_NR');
    }
}

