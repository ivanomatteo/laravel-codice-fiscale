<?php

namespace IvanoMatteo\LaravelCodiceFiscale;

use Illuminate\Support\Facades\Facade;

/**
 * @see \IvanoMatteo\LaravelCodiceFiscale\Skeleton\SkeletonClass
 */
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
