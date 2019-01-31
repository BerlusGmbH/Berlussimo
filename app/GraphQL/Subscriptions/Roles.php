<?php

namespace App\GraphQL\Subscriptions;

use Auth;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Roles extends GraphQLSubscription
{
    public const ROLE_ADDED = 'RoleAdded';
    public const ROLE_UPDATED = 'RoleUpdated';
    public const ROLE_DELETED = 'RoleDeleted';

    /**
     * Check if subscriber is allowed to listen to the subscription.
     *
     * @param  \Nuwave\Lighthouse\Subscriptions\Subscriber $subscriber
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function authorize(Subscriber $subscriber, Request $request)
    {
        return Auth::check();
    }

    /**
     * Filter subscribers who should receive subscription.
     *
     * @param  \Nuwave\Lighthouse\Subscriptions\Subscriber $subscriber
     * @param  mixed $root
     * @return bool
     */
    public function filter(Subscriber $subscriber, $root)
    {
        return true;
    }

    /**
     * Resolve the subscription.
     *
     * @param  mixed $root
     * @param  mixed[] $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context
     * @param  \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return mixed
     */
    public function resolve($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        return [
            'type' => Roles::ROLE_DELETED,
            'role' => $root
        ];
    }
}
