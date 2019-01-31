<?php

namespace App\Auditing;


use Carbon\Carbon;
use OwenIt\Auditing\Contracts\AttributeEncoder;

class DateEncoder implements AttributeEncoder
{
    /**
     * {@inheritdoc}
     */
    public static function encode($value)
    {
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function decode($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
    }
}