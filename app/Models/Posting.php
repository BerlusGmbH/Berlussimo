<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use Illuminate\Database\Eloquent\Model;

class Posting extends Model
{
    protected $table = 'GELD_KONTO_BUCHUNGEN';
    protected $primaryKey = 'GELD_KONTO_BUCHUNGEN_DAT';
    protected $externalKey = 'GELD_KONTO_BUCHUNGEN_ID';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
