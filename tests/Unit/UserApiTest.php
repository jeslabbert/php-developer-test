<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testUserApi()
    {
        $this->artisan('users:fetch')
            ->expectsOutput('Processing Completed')
            ->doesntExpectOutput('Processing Failed')
            ->assertExitCode(0);
    }
}
