<?php

namespace App\Providers;

use App\Models\BaustellenExtern;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Kaufvertraege;
use App\Models\Lager;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use App\Models\Wirtschaftseinheiten;
use App\Observers\DatabaseNotificationObserver;
use App\Services\ListViewsService;
use App\Services\PhoneLocator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\ServiceProvider;
use Schema;

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
            'Person' => Person::class,
            'PERSON' => Person::class,
            'OBJEKT' => Objekte::class,
            'HAUS' => Haeuser::class,
            'EINHEIT' => Einheiten::class,
            'Benutzer' => Person::class,
            'Partner' => Partner::class,
            'PARTNER' => Partner::class,
            'Einheit' => Einheiten::class,
            'Haus' => Haeuser::class,
            'Objekt' => Objekte::class,
            'EIGENTUEMER' => Kaufvertraege::class,
            'Eigentuemer' => Kaufvertraege::class,
            'BAUSTELLE_EXT' => BaustellenExtern::class,
            'Baustelle_ext' => BaustellenExtern::class,
            'MIETVERTRAG' => Mietvertraege::class,
            'Mietvertrag' => Mietvertraege::class,
            'WIRTSCHAFTSEINHEIT' => Wirtschaftseinheiten::class,
            'Wirtschaftseinheit' => Wirtschaftseinheiten::class,
            'LAGER' => Lager::class,
            'Lager' => Lager::class
        ]);

        Schema::defaultStringLength(191);

        DatabaseNotification::observe(DatabaseNotificationObserver::class);
        InvoiceLine::created(function ($invoiceLine) {
            Invoice::updateSums($invoiceLine);
        });
        InvoiceLine::updated(function ($invoiceLine) {
            Invoice::updateSums($invoiceLine);
        });
        InvoiceLine::deleted(function ($invoiceLine) {
            Invoice::updateSums($invoiceLine);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PhoneLocator::class, function () {
            return new PhoneLocator(config('phonelocator'));
        });
        $this->app->singleton(ListViewsService::class, function () {
            return new ListViewsService(config('listviews'));
        });
    }
}
