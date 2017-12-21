<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use DefaultOrder, Searchable;

    public $timestamps = false;
    protected $table = 'POSITIONEN_KATALOG';
    protected $primaryKey = 'KATALOG_ID';
    protected $defaultOrder = ['ARTIKEL_NR' => 'asc'];
    protected $searchableFields = ['ARTIKEL_NR', 'BEZEICHNUNG'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'invoice_item';
    }


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
        static::addGlobalScope('latest', function (Builder $query) {
            $query->whereIn('KATALOG_ID', function ($query) {
                $table = (new static())->table;
                $query->select(DB::raw('MAX(KATALOG_ID)'))
                    ->from($table)
                    ->groupBy('ART_LIEFERANT', 'ARTIKEL_NR');
            });
        });
    }

    public function supplier()
    {
        return $this->hasOne(Partner::class, 'PARTNER_ID', 'ART_LIEFERANT');
    }
}

