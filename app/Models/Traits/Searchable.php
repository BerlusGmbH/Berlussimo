<?php

namespace App\Models\Traits;


trait Searchable
{
    public static function search($query) {
        $tokens = static::tokenize($query);
        $builder = static::buildQuery($tokens);
        return $builder->get();
    }

    protected static function tokenize($query) {
        return explode(' ', $query);
    }

    protected static function buildQuery($tokens) {
        $builder = static::query();
        $model = $builder->getModel();
        foreach ($tokens as $token) {
            $builder->where(function ($q) use ($token, $model){
                foreach ($model->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $token . '%');
                }
            });
        }
        return $builder;
    }
}