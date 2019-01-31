<?php

namespace App\GraphQL\Mutations;

use App\Models\Bankkonten;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UpdateGlobalSelect
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
        if (isset($args['partnerId'])) {
            session()->put('partner_id', $args['partnerId']);
        } else {
            session()->remove('partner_id');
        }
        if (isset($args['propertyId'])) {
            session()->put('objekt_id', $args['propertyId']);
        } else {
            session()->remove('objekt_id');
        }
        if (isset($args['bankAccountId'])) {
            session()->put('geldkonto_id', $args['bankAccountId']);
            $property = Bankkonten::where('KONTO_ID', $args['bankAccountId'])->objekte()->first();
            if ($property) {
                session()->put('objekt_id', $property->id);
            }
        } else {
            session()->remove('geldkonto_id');
        }
        return [];
    }
}
