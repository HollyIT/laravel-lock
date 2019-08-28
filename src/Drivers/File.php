<?php

namespace Hollyit\LaravelLock\Drivers;

use Hollyit\LaravelLock\Contracts\LockDriver;
use Symfony\Component\Lock\Store\FlockStore;

class File implements LockDriver
{
    protected $path;

    public function __construct($options)
    {
        $this->path = $options['path'];
    }

    /**
     * @return \Symfony\Component\Lock\Store\FlockStore|\Symfony\Component\Lock\StoreInterface
     */
    public function getStore()
    {
        return new FlockStore($this->path);
    }
}
