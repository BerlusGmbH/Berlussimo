<?php

namespace App\Facades;


use App\Services\RelationsService;
use Illuminate\Support\Facades\Facade;

class Relations extends Facade
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
        return RelationsService::class;
    }

}