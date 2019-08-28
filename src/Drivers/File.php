<?php

namespace Hollyit\LaravelLock\Drivers;

use Symfony\Component\Lock\Store\FlockStore;
use Hollyit\LaravelLock\Contracts\LockDriver;

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
