<?php

namespace App\Models\Traits;

use DB;

trait HasEnum
{
    public static function getPossibleEnumValues($column)
    {
        // Create an instance of the model to be able to get the table name
        $instance = new static();

        // Pulls column string from DB
        $enumStr = DB::select(DB::raw('SHOW COLUMNS FROM ' . $instance->getTable() . ' WHERE Field = "' . $column . '"'))[0]['Type'];

        // Parse string
        preg_match_all("/'([^']+)'/", $enumStr, $matches);

        // Return matches
        return isset($matches[1]) ? $matches[1] : [];
    }
}