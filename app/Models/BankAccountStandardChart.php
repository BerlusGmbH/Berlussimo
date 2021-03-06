<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class BankAccountStandardChart extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'KONTENRAHMEN';
    protected $primaryKey = 'KONTENRAHMEN_ID';
    protected $searchableFields = ['NAME'];
    protected $defaultOrder = ['NAME' => 'asc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'bank_account_standard_chart';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function bookingAccounts()
    {
        return $this->hasMany(BookingAccount::class, 'EMPFANGS_GELD_KONTO');
    }
}

