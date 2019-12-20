<?php

namespace App\Models;

use App\Libraries\BelongsToMorph;
use App\Models\Scopes\AktuellScope;
use Illuminate\Database\Eloquent\Model;

class BankAccountAssociation extends Model
{
    public $timestamps = false;
    protected $table = 'GELD_KONTEN_ZUWEISUNG';
    protected $primaryKey = 'ZUWEISUNG_ID';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function property()
    {
        return BelongsToMorph::build(
            $this,
            Objekte::class,
            'property',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID'
        );
    }

    public function partner()
    {
        return BelongsToMorph::build(
            $this,
            Partner::class,
            'partner',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID'
        );
    }

    public function purchaseContract()
    {
        return BelongsToMorph::build(
            $this,
            Kaufvertraege::class,
            'purchaseContract',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID'
        );
    }

    public function bankAccount()
    {
        return $this->belongsTo(
            Bankkonten::class,
            'KONTO_ID',
            'KONTO_ID'
        );
    }
}
