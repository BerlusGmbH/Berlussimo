<?php

namespace App\GraphQL\Subscriptions;

use Auth;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;

class PersonUpdated extends GraphQLSubscription
{
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
        $args = $subscriber->args;
        return $root->id == $args['id'];
    }
}
