<?php

namespace App\GraphQL\Queries;

use Arr;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Houses
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
                            $query->orWhereHas('objekt', function ($query) use ($value) {
                                $query->where('id', $value['id']);
                            });
                    }
                }
            });
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
        $fieldSelection = $resolveInfo->getFieldSelection(1);
        if (Arr::has($fieldSelection, 'units.count')) {
            $result['units']['count'] = $rootValue->einheiten()->count();
        }
        return $result;
    }
}
