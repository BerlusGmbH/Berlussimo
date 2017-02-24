<?php

namespace App\Models\Traits;


trait Searchable
{
    public function scopeSearch($query, $string)
    {
        $this->buildQuery($query, $string);
        return $query;
    }

    protected function buildQuery($query, $string)
    {
        $model = $query->getModel();
        $query->where(function ($q) use ($string, $model) {
            foreach ($model->searchableFields as $field) {
                $q->orWhere($field, 'like', '%' . $string . '%');
            }
        });
        return $query;
    }
}