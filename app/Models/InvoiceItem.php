<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use DefaultOrder, Searchable;

    public $timestamps = false;
    protected $table = 'POSITIONEN_KATALOG';
    protected $primaryKey = 'KATALOG_DAT';
    protected $externalKey = 'KATALOG_ID';
    protected $defaultOrder = ['ARTIKEL_NR' => 'asc'];
    protected $searchableFields = ['ARTIKEL_NR', 'BEZEICHNUNG'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function supplier()
    {
        return $this->hasOne(Partner::class, 'id', 'ART_LIEFERANT');
    }
}

