<?php

namespace App\Providers;

use Event;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use PDO;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\TestEvent' => []
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
