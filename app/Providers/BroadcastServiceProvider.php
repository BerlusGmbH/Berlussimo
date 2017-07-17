<?php

namespace App\Providers;

use App\Libraries\NchanBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param BroadcastManager $broadcastManager
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $broadcastManager->extend('nchan', function (Application $app, array $config) {
            return new NchanBroadcaster($config);
        });
        require base_path('routes/channels.php');
    }
}