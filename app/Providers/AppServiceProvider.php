<?php

namespace App\Providers;

use App\Models\Bankkonten;
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
use App\Services\Immoware24ExportService;
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
            'Partner' => Partner::class,
            'Einheit' => Einheiten::class,
            'Haus' => Haeuser::class,
            'Objekt' => Objekte::class,
            'Eigentuemer' => Kaufvertraege::class,
            'Baustelle_ext' => BaustellenExtern::class,
            'Mietvertrag' => Mietvertraege::class,
            'Wirtschaftseinheit' => Wirtschaftseinheiten::class,
            'Lager' => Lager::class,
            'GELD_KONTEN' => Bankkonten::class
        ]);

        Schema::defaultStringLength(191);

        DatabaseNotification::observe(DatabaseNotificationObserver::class);
        InvoiceLine::created(function ($invoiceLine) {
            $invoice = Invoice::find($invoiceLine->BELEG_NR);
            if ($invoice) {
                $invoice->updateSums();
            }
        });
        InvoiceLine::updated(function ($invoiceLine) {
            $invoice = Invoice::find($invoiceLine->BELEG_NR);
            if ($invoice) {
                $invoice->updateSums();
            }
        });
        InvoiceLine::deleted(function ($invoiceLine) {
            $invoice = Invoice::find($invoiceLine->BELEG_NR);
            if ($invoice) {
                $invoice->updateSums();
            }
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
        $this->app->singleton(Immoware24ExportService::class, function () {
            return new Immoware24ExportService();
        });
    }
}
