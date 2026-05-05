<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmokeRoutesTest extends TestCase
{
    public function test_admin_login_page_returns_200(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_home_page_returns_200_when_database_is_installed(): void
    {
        if (! $this->applicationDatabaseHasMinimumSchema()) {
            $this->markTestSkipped(
                'Requires installed application schema (e.g. basic_settings, sections, languages). '.
                'SQLite in-memory from phpunit.xml has no tables until you migrate/seed or use a real DB for integration runs.'
            );
        }

        $response = $this->withSession(['currentLocaleCode' => 'en'])->get('/');

        $response->assertStatus(200);
    }

    public function test_listings_index_returns_200_when_database_is_installed(): void
    {
        if (! $this->applicationDatabaseHasMinimumSchema()) {
            $this->markTestSkipped(
                'Requires installed application schema. See test_home_page_returns_200_when_database_is_installed.'
            );
        }

        $response = $this->withSession(['currentLocaleCode' => 'en'])->get('/listings');

        $response->assertStatus(200);
    }

    private function applicationDatabaseHasMinimumSchema(): bool
    {
        try {
            return Schema::hasTable('basic_settings')
                && Schema::hasTable('sections')
                && Schema::hasTable('languages');
        } catch (\Throwable) {
            return false;
        }
    }
}
