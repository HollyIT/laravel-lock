<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'driver'      => env('LOCK_DRIVER', 'file'),
    'default_ttl' => 30,
    'drivers'     => [
        'file' => [
            'path' => env('LOCK_FILE_PATH', sys_get_temp_dir()),
        ],

        'redis' => [
            'prefix' => env('LOCK_REDIS_PREFIX', 'laravel_lock_'),
        ],

        'database' => [
            'table' => env('LOCK_DATABASE_TABLE', 'semaphore_locks'),
        ],
    ],
];
