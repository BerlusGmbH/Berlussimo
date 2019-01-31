<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class BookingAccount extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'KONTENRAHMEN_KONTEN';
    protected $primaryKey = 'KONTENRAHMEN_KONTEN_DAT';
    protected $externalKey = 'KONTENRAHMEN_KONTEN_ID';
    protected $searchableFields = ['KONTO', 'BEZEICHNUNG'];
    protected $defaultOrder = [
        'KONTENRAHMEN_ID' => 'asc',
        'KONTO' => 'asc',
        'BEZEICHNUNG' => 'asc'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function bankAccountStandardChart()
    {
        return $this->belongsTo(BankAccountStandardChart::class, 'KONTENRAHMEN_ID', 'KONTENRAHMEN_ID');
    }
}

