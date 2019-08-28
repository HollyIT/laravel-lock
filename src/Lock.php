<?php

namespace Hollyit\LaravelLock;

use Exception;
use Symfony\Component\Lock\Factory;
use Hollyit\LaravelLock\Drivers\File;
use Hollyit\LaravelLock\Drivers\Redis;
use Hollyit\LaravelLock\Drivers\Database;
use Illuminate\Contracts\Foundation\Application;

class Lock
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * Lock constructor.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param $config
     */
    public function __construct(Application $app, $config)
    {
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * @return \Symfony\Component\Lock\Factory
     * @throws \Exception
     */
    public function getFactory()
    {
        if (! $this->factory) {
            $this->factory = $this->makeFactory();
        }

        return $this->factory;
    }

    /**
     * @param  null  $driver
     * @param  array  $driverOptions
     * @return \Symfony\Component\Lock\Factory
     * @throws \Exception
     */
    public function makeFactory($driver = null, $driverOptions = [])
    {
        $driver = $driver ?: $this->config['driver'];
        $driverOptions = $driverOptions ?: $this->config['drivers'][$driver];
        if (! $driverOptions) {
            throw new Exception('Undefined lock driver options for '.$driver);
        }

        $driver = $this->getDriver($driver, $driverOptions);

        return new Factory($driver);
    }

    /**
     * @param $driver
     * @param $driverOptions
     * @return \Symfony\Component\Lock\StoreInterface
     * @throws \Exception
     */
    public function getDriver($driver, $driverOptions)
    {
        $defaultDrivers = [
            'file'     => File::class,
            'redis'    => Redis::class,
            'database' => Database::class,
        ];

        $class = ! empty($driverOptions['class']) ? $driverOptions['class'] : $defaultDrivers[$driver];

        if (! $class || ! class_exists($class)) {
            throw new Exception('Unable to determine lock driver class for '.$driver);
        }

        return $this->app->make($class, ['options' => $driverOptions])
            ->getStore();
    }

    /**
     * @param $name
     * @param  null  $ttl
     * @return \Symfony\Component\Lock\Lock
     * @throws \Exception
     */
    public function make($name, $ttl = null)
    {
        $ttl = $ttl ?: $this->config['default_ttl'];

        return $this->getFactory()
            ->createLock($name, $ttl);
    }
}
