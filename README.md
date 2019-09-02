# Laravel Lock

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hollyit/laravel-lock.svg?style=flat-square)](https://packagist.org/packages/hollyit/laravel-lock)
[![Build Status](https://img.shields.io/travis/hollyit/laravel-lock/master.svg?style=flat-square)](https://travis-ci.org/hollyit/laravel-lock)

##Description

A simple Laravel wrapper for the [Symfony lock](https://symfony.com/doc/current/components/lock.html) component to provide semaphore locking.

## Why?
When you have routines that take a longer time than usual to run, you may not want other processes
attempting to run it. To prevent this you can acquire what is referred to as a lock or semaphore. 
This prevents problems of [contention](https://en.wikipedia.org/wiki/Resource_contention).


## Installation

You can install the package via composer:

```bash
composer require hollyit/laravel-lock
```

## Usage

``` php
$lock = LaravelLock::make('test-lock')
if ($lock->acquire()) {
  // Perform my long running task
  $lock->release();
} else {
  // Something is already running on this process so let's not do anything.
}
```

### Currently supported drivers

Drivers can be configured in config/lock.php. View that file for additional details.
To publish the configuration
```bash
php artisan vendor:publish --provider="Hollyit\LaravelLock\LaravelLockServiceProvider" --tag="config"
```
**File Drive:**

*(This is a wrapper of the [Symfony FlockStore](https://symfony.com/doc/current/components/lock.html#flockstore))*

No additional setup required. By default this will use the system's temporary directory. 
You may override this in config or environment. Please see config/lock.php for details.

With the exception of Database, drivers are very simple wrappers. If you would like to
add additional drivers, please consider a PR.

**Database driver:**

*(This is a modification of the [PdoStore](https://symfony.com/doc/current/components/lock.html#lock-store-pdo), 
modified to use Laravel's database layer)*

This will use your default database connection. Before using this driver you must publish and migrate your database.

```bash
php artisan vendor:publish --provider="Hollyit\LaravelLock\LaravelLockServiceProvider" --tag="migraions"
```
If you wish to use a custom table name, then please modify your config/lock.php file prior to migrating.

**Redis:**

Redis isn't currently supported, but it will in a future release.

**Custom drivers:**

Other [stores](https://symfony.com/doc/current/components/lock.html#available-stores) from the Symfony/lock package aren't currently supported. Migrating them over should be
rather simple if you would like to (and please submit a PR). 

You may also define custom drivers outside of what the original symfony/lock package offers. Under your config/lock.php
configuration file, simple define a 'class' property to point to the class of the new driver. Drivers must implement the
\Hollyit\LaravelLock\Contracts\LockDriver contract.

### Additional details

For additional details and features, please refer to the Symfony Lock [documentation](https://symfony.com/doc/current/components/lock.html).
### Testing
***NOTE**: Testing is extremely limited at this time given the need for this package on a client project.
If you are willing to expand on test coverage, please do so and submit a PR.*
``` bash
composer test
```

## Contributing

This package was quickly developed due to the need of a current client project. If you would like to add
more drivers, features or tests, then your help will be greatly welcomed. 

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email jamie@hollyit.net instead of using the issue tracker.

## Credits

- [Jamie Holly](https://github.com/hollyit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
