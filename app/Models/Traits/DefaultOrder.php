<?php

namespace App\Models\Traits;


trait DefaultOrder
{
    public function scopeDefaultOrder($query) {
        if(isset($this->defaultOrder)) {
            foreach($this->defaultOrder as $field => $order) {
                $query->orderBy($field, $order);
            }
        }
        return $query;
    }
}