<?php

namespace App\Facades;

use App\Services\ListViewsService;
use Illuminate\Support\Facades\Facade;

class ListViews extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return ListViewsService::class;
    }

}