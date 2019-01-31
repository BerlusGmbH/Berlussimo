<?php


namespace App\GraphQL\Queries;


use App\Services\PhoneLocator;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class State
{
    protected $phoneLocator;

    public function __construct(PhoneLocator $phoneLocator)
    {
        $this->phoneLocator = $phoneLocator;
    }

    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return [];
    }

    public function resolvePhoneAtWorkplace($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return $this->phoneLocator->workplaceHasPhone();
    }

    public function resolveCsrf($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return csrf_token();
    }
}