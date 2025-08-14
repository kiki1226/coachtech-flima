<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    public function testBasicExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    // どれか確実に表示される文言に合わせる
                    ->assertSee('商品一覧');       // 例：商品一覧
                    // ->assertSee('COACHTECH');   // でもOK
        });
    }
}
