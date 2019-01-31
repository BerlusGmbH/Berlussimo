<?php

namespace App\Providers;

use App\Validators\AssignedQuantityLessThanOrEqualToQuantity;
use App\Validators\NoOtherContract;
use App\Validators\QuantityGreaterThanOrEqualToAssignedQuantity;
use Illuminate\Support\ServiceProvider;
use Validator;

class GraphQlValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend(
            'noOtherContract',
            'App\\Validators\\NoOtherContract@validate',
            NoOtherContract::message()
        );
        Validator::extend(
            'quantityGreaterThanOrEqualToAssignedQuantity',
            'App\\Validators\\QuantityGreaterThanOrEqualToAssignedQuantity@validate',
            QuantityGreaterThanOrEqualToAssignedQuantity::message()
        );
        Validator::extend(
            'assignedQuantityLessThanOrEqualToQuantity',
            'App\\Validators\\AssignedQuantityLessThanOrEqualToQuantity@validate',
            AssignedQuantityLessThanOrEqualToQuantity::message()
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
