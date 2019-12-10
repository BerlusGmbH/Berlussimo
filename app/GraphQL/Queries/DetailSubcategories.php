<?php

namespace App\GraphQL\Queries;

use App\Models\DetailSubcategory;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DetailSubcategories
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (Arr::exists($args, 'category') && Arr::exists($args, 'detailableType')) {

            return DetailSubcategory::whereHas('category', function ($query) use ($args) {
                $query->where(
                    'DETAIL_KAT_KATEGORIE',
                    $args['detailableType']
                )->where(
                    'DETAIL_KAT_NAME',
                    $args['category']
                );
            })->get();
        }
        return DetailSubcategory::all();
    }
}
