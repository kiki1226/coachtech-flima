<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $bootstrapped = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Dusk 実行時だけ初期化（.env.dusk.local を使用）
        if (app()->environment('dusk')) {
            if (! static::$bootstrapped) {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]); // ←テーブル作成→シードの順
                Artisan::call('storage:link', ['--force' => true]);                   // 画像用
                static::$bootstrapped = true;
            }
            // 毎テストで初期データを入れ直したいなら下を有効化（通常は不要・重くなる）
            // Artisan::call('db:seed', ['--force' => true]);
        }
    }

    /** @beforeClass */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge(['--disable-gpu', '--headless']);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );
    }

    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) || isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) || isset($_ENV['DUSK_START_MAXIMIZED']);
    }
}
