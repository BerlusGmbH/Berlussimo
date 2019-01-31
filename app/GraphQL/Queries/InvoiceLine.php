<?php

namespace App\GraphQL\Queries;

use App\Models\InvoiceLineAssignment;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class InvoiceLine
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function assignments($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (isset($rootValue)) {
            return InvoiceLineAssignment::where('BELEG_NR', $rootValue->BELEG_NR)
                ->where('POSITION', $rootValue->POSITION)->get();
        }
        return [];
    }
}
