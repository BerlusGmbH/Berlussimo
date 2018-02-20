<?php

namespace App\Models\Traits;

use objekt;

trait CopyObject
{
    public function copy($owner, $name, $prefix, $opening_balance, $opening_balance_date)
    {
        $object = new objekt();
        //hack o make legacy code work
        error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
        $object_id = $object->objekt_kopieren($this->OBJEKT_ID, $owner, $name, $prefix, $opening_balance_date, $opening_balance);
        error_reporting(E_ALL);
        return $object_id;
    }
}