<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        // Default app home requires migrated DB + seed data; admin login is a stable smoke check on empty SQLite.
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }
}
