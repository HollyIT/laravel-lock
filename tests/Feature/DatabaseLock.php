<?php

namespace Hollyit\LaravelLock\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Hollyit\LaravelLock\Tests\LockTest;

class DatabaseLock extends LockTest
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('lock.driver', 'database');
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(realpath(__DIR__.'/../../migrations/2019_08_28_162752_create_lock_table.php'));
    }

    /** @test */
    public function it_acquires_a_file_lock()
    {
        $this->withoutExceptionHandling();
        $lock = $this->lockService()
            ->make('test');

        $this->assertTrue($lock->acquire());
        $lock2 = $this->lockService()
            ->make('test');
        $this->assertFalse($lock2->acquire());

        // Make sure we actually wrote to the database.
        $record = DB::table('semaphore_locks')
            ->first();
        $this->assertNotNull($record);
        $lock->release();

        // Now make sure we actually dropped from the database
        $this->assertNull(DB::table('semaphore_locks')
            ->where('id', $record->id)
            ->first());
    }
}
