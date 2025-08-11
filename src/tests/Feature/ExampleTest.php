<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function top_page_returns_200()
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function tests_run_on_sqlite_testing_db()
    {
        $this->assertSame('testing', app()->environment());        // testing で動いてる？
        $this->assertSame('sqlite', config('database.default'));   // DB は sqlite？
    }
}
