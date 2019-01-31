<?php

namespace App\GraphQL;

use Nuwave\Lighthouse\Support\Http\Controllers\SubscriptionController;

class RoutesRegister
{
    /**
     * Register subscription routes.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function register($router)
    {
        $router->post('graphql/subscriptions/auth', [
            'as' => 'lighthouse.subscriptions.auth',
            'uses' => SubscriptionController::class . '@authorize',
            'middleware' => 'auth:api'
        ]);

        $router->post('graphql/subscriptions/webhook', [
            'as' => 'lighthouse.subscriptions.auth',
            'uses' => SubscriptionController::class . '@webhook',
            'middleware' => 'auth:api'
        ]);
    }
}
