<?php

namespace Hollyit\LaravelLock;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hollyit\LaravelLock\Lock
 */
class LockFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Lock::class;
    }
}
