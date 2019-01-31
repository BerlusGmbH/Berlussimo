<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Notifications\DatabaseNotification;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MarkAllNotificationsAsUnread
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
        $ids = $context->request()->user('api')->notifications()->whereNull('read_at')->get('id')->pluck('id');
        $context->request()->user('api')->notifications()->whereNull('read_at')->update(
            ['read_at' => null]
        );
        return DatabaseNotification::whereIn('id', $ids)->get();
    }
}
