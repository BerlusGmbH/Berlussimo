<?php

namespace App\GraphQL\Queries;

use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Units
{
    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function search($builder, $value)
    {
        if (!is_null($value)) {
            $builder->search($value);
        }
        return $builder;
    }


    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return array
     */
    public function aggregations($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $result = [];
        return $result;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function rentalContracts($builder, $values)
    {
        if (!is_array($values) || count(array_keys($values)) === 0) {
            return $builder;
        }
        $builder->whereHas('mietvertraege', function ($query) use ($values) {
            $query->where(function ($query) use ($values) {
                if (array_key_exists('tenants', $values) && is_array($values['tenants'])) {
                    foreach ($values['tenants'] as $value) {
                        switch ($value['type']) {
                            case "Person":
                                $query->orWhereHas('mieter', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                        }
                    }
                }
                if (array_key_exists('active', $values) && isset($values['active'])) {
                    $query->active('=', $values['active']);
                }
            });
        });
        return $builder;
    }

    /* Limit result to units matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function vacant($builder, $value)
    {
        if (!$value instanceof Carbon) {
            return $builder;
        }
        $builder->whereDoesntHave('mietvertraege', function ($query) use ($value) {
            $query->where(function ($query) use ($value) {
                $query->active('=', $value);
            });
        });
        return $builder;
    }

    /* Limit result to units matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function partOf($builder, $values)
    {
        if (is_array($values)) {
            $builder->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    switch ($value['type']) {
                        case "Property":
                            $query->orWhereHas('haus.objekt', function ($query) use ($value) {
                                $query->where('id', $value['id']);
                            });
                            break;
                        case "House":
                            $query->orWhereHas('haus', function ($query) use ($value) {
                                $query->where('id', $value['id']);
                            });
                            break;
                        case "Unit":
                            $query->orWhere('id', $value['id']);
                    }
                }
            });
        }
        return $builder;
    }
}
