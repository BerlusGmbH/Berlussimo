<?php


namespace App\GraphQL\Queries;


use App\Models\Bankkonten;
use App\Models\Objekte;
use App\Models\Partner;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GlobalSelect
{
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return [];
    }

    public function resolvePartner($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (session()->has('partner_id')) {
            return Partner::where('id', session()->get('partner_id'))->first();
        }
        return null;
    }

    public function resolveProperty($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (session()->has('objekt_id')) {
            return Objekte::where('id', session()->get('objekt_id'))->first();
        }
        return null;
    }

    public function resolveBankAccount($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (session()->has('geldkonto_id')) {
            return Bankkonten::where('KONTO_ID', session()->get('geldkonto_id'))->first();
        }
        return null;
    }
}
