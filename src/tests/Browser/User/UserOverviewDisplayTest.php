<?php

namespace Tests\Browser\User;

use App\Models\User;
use App\Models\Product;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserOverviewDisplayTest extends DuskTestCase
{
    

    private const BASE   = 'http://nginx';
    private const LOGIN  = '/login';
    private const MYPAGE = '/mypage';
    private const WAIT   = 7;

    public function test_user_profile_image_name_and_lists_are_visible(): void
    {
        $user = User::factory()->create([
            'name'           => 'テスト 太郎',
            'email'          => 'taro@example.com',
            'password'       => bcrypt('password'),
            'is_profile_set' => true,
        ]);

        $sellA = Product::factory()->create(['name' => '出品商品A', 'user_id' => $user->id]);
        $sellB = Product::factory()->create(['name' => '出品商品B', 'user_id' => $user->id]);

        $seller = User::factory()->create();
        $buyX = Product::factory()->create(['name' => '購入商品X', 'user_id' => $seller->id, 'buyer_id' => $user->id]);
        $buyY = Product::factory()->create(['name' => '購入商品Y', 'user_id' => $seller->id, 'buyer_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $sellA, $sellB, $buyX, $buyY) {
            $browser->visit(self::BASE . self::LOGIN)
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('ログイン')
                ->visit(self::BASE . self::MYPAGE)
                ->assertUrlIs(self::BASE . self::MYPAGE)
                ->assertSee($user->name);

            // 画像確認
            $browser->assertPresent('img.avatar');
            $src = $browser->attribute('img.avatar', 'src');
            $this->assertNotEmpty($src, 'プロフィール画像の src が空です');

            // 出品一覧
            $browser->visit(self::BASE . self::MYPAGE . '?tab=sell')
                ->waitForText($sellA->name, self::WAIT)
                ->assertSee($sellA->name)
                ->assertSee($sellB->name);

            // 購入一覧
            $browser->visit(self::BASE . self::MYPAGE . '?tab=buy')
                ->waitForText($buyX->name, self::WAIT)
                ->assertSee($buyX->name)
                ->assertSee($buyY->name);
        });
    }
}
