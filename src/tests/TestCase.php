<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase; // ← 全テストでDBを用意

    protected function setUp(): void
    {
        parent::setUp();

        // testing用に必ずスキーマを作って、初期データを投入
        $this->artisan('migrate');   // = migrate --env=testing
        $this->seed();               // DatabaseSeeder（画像コピーもここで一度走る）
    }
}
