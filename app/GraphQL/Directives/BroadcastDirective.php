<?php

namespace App\GraphQL\Directives;


use Closure;
use GraphQL\Deferred;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Schema\Directives\BroadcastDirective as BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;

class BroadcastDirective extends BaseDirective
{
    /**
     * Resolve the field directive.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue $value
     * @param  \Closure $next
     *
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function handleField(FieldValue $value, Closure $next): FieldValue
    {
        $value = $next($value);
        $resolver = $value->getResolver();
        $subscriptionField = $this->directiveArgValue('subscription');
        $shouldQueue = $this->directiveArgValue('shouldQueue');

        if (!is_array($subscriptionField)) {
            $subscriptionField = [$subscriptionField];
        }

        foreach ($subscriptionField as $subscription) {
            $value->setResolver(function () use ($resolver, $subscription, $shouldQueue) {
                $resolved = call_user_func_array($resolver, func_get_args());

                if ($resolved instanceof Deferred) {
                    $resolved->then(function ($root) use ($subscription, $shouldQueue) {
                        Subscription::broadcast($subscription, $root, $shouldQueue);
                    });
                } else {
                    Subscription::broadcast($subscription, $resolved, $shouldQueue);
                }

                return $resolved;
            });
        }
        return $value;
    }
}