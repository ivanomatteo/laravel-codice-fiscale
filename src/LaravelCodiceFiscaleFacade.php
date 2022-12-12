<?php

namespace IvanoMatteo\LaravelCodiceFiscale;

use Illuminate\Support\Facades\Facade;

class LaravelCodiceFiscaleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-codice-fiscale';
    }
}
