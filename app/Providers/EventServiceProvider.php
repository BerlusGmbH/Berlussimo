<?php

namespace App\Providers;

use App\Listeners\AuditsSubscriptionBroadcast;
use Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use OwenIt\Auditing\Events\Audited;
use PDO;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Audited::class => [
            AuditsSubscriptionBroadcast::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        });
    }
}
