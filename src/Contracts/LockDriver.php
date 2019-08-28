<?php

namespace Hollyit\LaravelLock\Contracts;

interface LockDriver
{
    /**
     * @return \Symfony\Component\Lock\Store\FlockStore|\Symfony\Component\Lock\StoreInterface
     */
    public function getStore();
}
