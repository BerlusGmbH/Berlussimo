<?php

namespace App\Providers;

use App\Models\Policies\DatabaseNotificationPolicy;
use Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        DatabaseNotification::class => DatabaseNotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Auth::provider('berlussimo', function ($app, $config) {
            return new BerlussimoUserProvider($app['hash'], $config['model']);
        });
        Passport::routes();
    }
}
