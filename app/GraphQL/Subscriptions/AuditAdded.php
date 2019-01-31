<?php

namespace App\GraphQL\Subscriptions;

use App\Models\Person;
use Auth;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use OwenIt\Auditing\Models\Audit;

class AuditAdded extends GraphQLSubscription
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
        if ($root instanceof Audit) {
            if ($root->auditable instanceof Person) {
                return $root->auditable->id == $args['personId'];
            }
        }
        return false;
    }
}
