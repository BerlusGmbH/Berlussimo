<?php

namespace App\GraphQL\Subscriptions;

use App\Models\Person;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;

class NotificationAdded extends GraphQLSubscription
{
    /**
     * Check if subscriber is allowed to listen to the subscription.
     *
     * @param Subscriber $subscriber
     * @param Request $request
     * @return bool
     */
    public function authorize(Subscriber $subscriber, Request $request)
    {
        return Auth::check();
    }

    /**
     * Filter subscribers who should receive subscription.
     *
     * @param Subscriber $subscriber
     * @param mixed $root
     * @return bool
     */
    public function filter(Subscriber $subscriber, $root)
    {
        if ($root instanceof DatabaseNotification && $root->notifiable instanceof Person) {
            return $root->notifiable->id === $subscriber->context->user->id;
        }
        return false;
    }
}
