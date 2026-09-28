<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_on_the_testing_database(): void
    {
        $this->assertTrue(true);
    }
}
