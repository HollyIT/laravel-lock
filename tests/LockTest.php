<?php

namespace Hollyit\LaravelLock\Tests;

use Hollyit\LaravelLock\LaravelLockServiceProvider;
use Hollyit\LaravelLock\Lock;
use Orchestra\Testbench\TestCase;

class LockTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelLockServiceProvider::class];
    }

    /**
     * @return Lock
     */
    public function lockService()
    {
        return $this->app[Lock::class];
    }
}
