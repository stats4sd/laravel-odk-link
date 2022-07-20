<?php

namespace Stats4sd\OdkLink\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Stats4sd\OdkLink\OdkLink
 */
class OdkLink extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-odk-link';
    }
}
