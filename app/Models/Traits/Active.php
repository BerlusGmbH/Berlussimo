<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait Active
{
    public function scopeActive($query, $comparator = '=', $date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        } elseif (is_string($date)) {
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
                                ->orWhereDate($end, '=', '9999-12-31')
                                ->orWhereNull($end);
                        });
                });
                break;
            case '>':
                $query->where(function ($query) use ($date, $end) {
                    $query->whereDate($end, '>', $date)
                        ->orWhereDate($end, '=', '0000-00-00')
                        ->orWhereDate($end, '=', '9999-12-31')
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
                        ->orWhereDate($end, '=', '9999-12-31')
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

    public function getActiveAttribute()
    {
        return $this->isActive();
    }

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
                            $this->{$end} == '0000-00-00'
                            || is_null($this->{$end})
                            || $this->{$end} == '9999-12-31'
                        )
                    );
            case '>':
                return $this->{$end} > $date
                    || (
                        $this->{$end} == '0000-00-00'
                        || is_null($this->{$end})
                        || $this->{$end} == '9999-12-31'
                    );
            case '<':
                return $this->{$start} < $date;
            case '>=':
                return $this->{$end} >= $date
                    || (
                        $this->{$end} == '0000-00-00'
                        || is_null($this->{$end})
                        || $this->{$end} == '9999-12-31'
                    );
            case '<=':
                return $this->{$start} <= $date;
            default:
                return false;
        }
    }

    public function overlaps(Carbon $start, Carbon $end)
    {
        $start = Carbon::parse($this->{$this->getStartDateFieldName()})->max($start);
        $end_field = ($this->{$this->getEndDateFieldName()} === '0000-00-00' || is_null($this->{$this->getEndDateFieldName()})) ? Carbon::maxValue()
            : Carbon::parse($this->{$this->getEndDateFieldName()});
        $end = $end_field->min($end);
        if ($start->lte($end)) {
            return $start->diffInDays($end) + 1;
        }
        return 0;
    }
}