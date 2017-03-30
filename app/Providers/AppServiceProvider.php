<?php

namespace App\Providers;

use App\Models\BaustellenExtern;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Lager;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use App\Models\User;
use App\Models\Wirtschaftseinheiten;
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
            'PERSON' => Person::class,
            'OBJEKT' => Objekte::class,
            'HAUS' => Haeuser::class,
            'EINHEIT' => Einheiten::class,
            'Benutzer' => User::class,
            'Partner' => Partner::class,
            'Einheit' => Einheiten::class,
            'Haus' => Haeuser::class,
            'Objekt' => Objekte::class,
            'Eigentuemer' => Kaufvertraege::class,
            'Baustelle_ext' => BaustellenExtern::class,
            'Mietvertrag' => Mietvertraege::class,
            'Wirtschaftseinheit' => Wirtschaftseinheiten::class,
            'Lager' => Lager::class
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PhoneLocator::class, function() {
            return new PhoneLocator(config('phonelocator'));
        });
    }
}
