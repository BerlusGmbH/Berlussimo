<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Notifications
{

    /**
     * Resolve the subscription.
     *
     * @param mixed $root
     * @param mixed[] $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return mixed
     */
    public function resolve($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return $context->request()->user('api')->notifications()->get();
    }
}
