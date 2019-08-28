<?php

namespace Hollyit\LaravelLock\Tests\Feature;

use Hollyit\LaravelLock\Tests\LockTest;

class FileLock extends LockTest
{
    /** @test */
    public function it_acquires_a_file_lock()
    {
        $lock = $this->lockService()
            ->make('test');

        $this->assertTrue($lock->acquire());
        $lock2 = $this->lockService()
            ->make('test');
        $this->assertFalse($lock2->acquire());
        $lock2->release();
    }
}
