<?php

namespace App\Listeners;


use Nuwave\Lighthouse\Execution\Utils\Subscription;
use OwenIt\Auditing\Events\Audited;

class AuditsSubscriptionBroadcast
{
    /**
     * Handle the Audited event.
     *
     * @param \OwenIt\Auditing\Events\Audited $event
     * @return void
     */
    public function handle(Audited $event)
    {
        Subscription::broadcast('auditAdded', $event->audit);
    }
}