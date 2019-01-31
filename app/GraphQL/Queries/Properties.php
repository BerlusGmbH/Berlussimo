<?php

namespace App\GraphQL\Queries;

use Arr;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Properties
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
        $fieldSelection = $resolveInfo->getFieldSelection(1);
        if (Arr::has($fieldSelection, 'houses.count')) {
            $result['houses']['count'] = $rootValue->haeuser()->count();
        }
        if (Arr::has($fieldSelection, 'units.count')) {
            $result['units']['count'] = $rootValue->einheiten()->count();
        }
        return $result;
    }
}
