<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait Active
{
    public function isActive($comparator = '=', Carbon $date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        $start = $this->getStartDateFieldName();
        $end = $this->getEndDateFieldName();

        switch ($comparator) {
            case '=':
                return $this->{$start} <= $date
                    && (
                        $this->{$end} >= $date
                        || (
                            $this->{$end} == '0000-00-00' || is_null($this->{$end}
                            )
                        )
                    );
            case '>':
                return $this->{$end} > $date
                    || (
                        $this->{$end} == '0000-00-00' || is_null($this->{$end})
                    );
            case '<':
                return $this->{$start} < $date;
            case '>=':
                return $this->{$end} >= $date
                    || (
                        $this->{$end} == '0000-00-00' || is_null($this->{$end})
                    );
            case '<=':
                return $this->{$start} <= $date;
            default:
                return false;
        }
    }

    public function scopeActive($query, $comparator = '=', $date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        } else {
            $date = Carbon::parse($date);
        }
        $start = $this->getStartDateFieldName();
        $end = $this->getEndDateFieldName();

        switch ($comparator) {
            case '=':
                $query->where(function ($query) use ($date, $start, $end) {
                    $query->whereDate($start, '<=', $date)
                        ->where(function ($query) use ($date, $end) {
                            $query->whereDate($end, '>=', $date)
                                ->orWhereDate($end, '=', '0000-00-00')
                                ->orWhereNull($end);
                        });
                });
                break;
            case '>':
                $query->where(function ($query) use ($date, $end) {
                    $query->whereDate($end, '>', $date)
                        ->orWhereDate($end, '=', '0000-00-00')
                        ->orWhereNull($end);
                });
                break;
            case '<':
                $query->whereDate($start, '<', $date);
                break;
            case '>=':
                $query->where(function ($query) use ($date, $end) {
                    $query->whereDate($end, '>=', $date)
                        ->orWhereDate($end, '=', '0000-00-00')
                        ->orWhereNull($end);
                });
                break;
            case '<=':
                $query->whereDate($start, '<=', $date);
                break;
        }
    }

    public function scopeNotActive($query, $comparator = '=', $date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        } else {
            $date = Carbon::parse($date);
        }
        $start = $this->getStartDateFieldName();
        $end = $this->getEndDateFieldName();

        switch ($comparator) {
            case '=':
                $query->where(function ($query) use ($date, $start, $end) {
                    $query->whereDate($start, '>', $date)
                        ->orWhereDate($end, '<', $date)
                        ->where($end, '<>', '0000-00-00');
                });
                break;
            case '>':
                $query->whereDate($end, '<', $date)->where($end, '<>', '0000-00-00');
                break;
            case '<':
                $query->whereDate($start, '>', $date);
                break;
            case '>=':
                $query->whereDate($end, '<=', $date)->where($end, '<>', '0000-00-00');
                break;
            case '<=':
                $query->whereDate($start, '>=', $date);
                break;
        }
    }
}