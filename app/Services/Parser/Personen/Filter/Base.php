<?php

namespace App\Services\Parser\Personen\Filter;


use App\Models\Personen;

class Base
{
    protected function einheitLageEqValue($val)
    {
        return Personen::where(function ($query) use ($val) {
            $query->whereHas('mietvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_LAGE', '=', $val);
            })->orWhereHas('kaufvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_LAGE', '=', $val);
            });
        });
    }

    protected function einheitNameLikeValue($val)
    {
        return Personen::where(function ($query) use ($val) {
            $query->whereHas('mietvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_KURZNAME', 'like', '%' . $val . '%');
            })->orWhereHas('kaufvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_KURZNAME', 'like', '%' . $val . '%');
            });
        });
    }

    protected function einheitNameEqValue($val)
    {
        return Personen::where(function ($query) use ($val) {
            $query->whereHas('mietvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_KURZNAME', '=', $val);
            })->orWhereHas('kaufvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_KURZNAME', '=', $val);
            });
        });
    }

    protected function einheitLageLikeValue($val)
    {
        return Personen::where(function ($query) use ($val) {
            $query->whereHas('mietvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_LAGE', 'like', '%' . $val . '%');
            })->orWhereHas('kaufvertraege.einheit', function ($query) use ($val) {
                $query->where('EINHEIT_LAGE', 'like', '%' . $val . '%');
            });
        });
    }
}