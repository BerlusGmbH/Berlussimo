<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HOAFeeDefinition extends Model
{
    protected $table = 'WEG_WG_DEF';
    protected $primaryKey = 'ID';

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

    public static function sumDefinitions($query, $from = null, $to = null)
    {
        return $query->get()->reduce(function ($carry, $item) use ($from, $to) {
            return $carry + $item->value($from, $to);
        }, 0);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function value($from = null, $to = null)
    {
        $definition_from = Carbon::parse($this->ANFANG, 'UTC');
        $definition_to = $this->ENDE === '0000-00-00' ? Carbon::maxValue() : Carbon::parse($this->ENDE, 'UTC');
        if (is_null($from)) {
            $from = $definition_from;
        }
        if (is_null($to)) {
            $to = $definition_to;
        }
        if ($from instanceof Carbon && $from->tzName !== 'UTC') {
            $from = Carbon::parse($from->toDateString(), 'UTC');
        }
        if ($to instanceof Carbon && $to->tzName !== 'UTC') {
            $to = Carbon::parse($to->toDateString(), 'UTC');
        }
        if (is_string($from)) {
            $from = Carbon::parse($from, 'UTC');
        }
        if (is_string($to)) {
            $to = Carbon::parse($to, 'UTC');
        }
        $from = $definition_from->max($from);
        $to = $definition_to->min($to);
        $diffInMonths = $from->diffInMonths($to);
        $value = $this->BETRAG * ($diffInMonths + 1);
        //TODO one fine day get rid of this inversion inconsistency
        return $value;
    }
}
