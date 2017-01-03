<?php

namespace App\Providers;

use App\Pagination\MaterializeCssPresenter;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\PhoneLocator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'PERSON' => \App\Models\Personen::class
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Paginator::presenter(function ($paginator) {
            return new MaterializeCssPresenter($paginator);
        });
        $this->app->singleton(PhoneLocator::class, function() {
            return new PhoneLocator(config('phonelocator.map'));
        });
    }
}
