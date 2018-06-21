<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RentDefinition extends Model
{
    protected $table = 'MIETENTWICKLUNG';
    protected $primaryKey = 'MIETENTWICKLUNG_ID';

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

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('MIETENTWICKLUNG_AKTUELL', '1');
        });
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
        $value = 0;
        if ($definition_from->diffInMonths($definition_to) == 0) {
            $value = $this->BETRAG;
        } else {
            if ($diffInMonths == 0) {
                $value = (($from->diffInDays($to) + 1) / $from->daysInMonth) * $this->BETRAG;
            } else {
                if ($from->day == 1) {
                    $value += $this->BETRAG;
                } else {
                    $value += ($from->day / $from->daysInMonth) * $this->BETRAG;
                }
                if ($to->day == $to->daysInMonth) {
                    $value += $this->BETRAG;
                } else {
                    $value += ($to->day / $to->daysInMonth) * $this->BETRAG;
                }
                $value += $this->BETRAG * ($diffInMonths - 1);
            }
        }
        return $value;
    }
}
