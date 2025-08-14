<?php

namespace Tests\Browser\Product;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\File;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductShowSoldStateTest extends DuskTestCase
{
    

    private const BASE      = 'http://nginx';
    private const LOGIN     = '/login';
    private const SHOW_PATH = '/item/';
    private const WAIT      = 12;

    public function test_sold_product_shows_label_and_hides_buy_button(): void
    {
        // ダミー画像（src が通れば OK）
        $img = public_path('storage/uploads/products/sold.jpg');
        if (!is_dir(dirname($img))) mkdir(dirname($img), 0777, true);
        if (!file_exists($img)) file_put_contents($img, 'dummy');

        $seller = User::factory()->create();
        $buyer  = User::factory()->create(['email_verified_at' => now(), 'password' => bcrypt('password')]);

        $product = Product::factory()->create([
            'user_id'    => $seller->id,
            'name'       => '売り切れ商品',
            'price'      => 9999,
            'description'=> '売り切れのテスト商品',
            'condition'  => '良好',
            'image_path' => 'storage/uploads/products/sold.jpg',
        ]);

        // 🔴 売り切れ状態を確実に作る（両対応）
        $product->forceFill([
            'buyer_id' => $buyer->id, // ← モデルが buyer_id で is_sold を算出する場合
            'is_sold'  => true,       // ← 物理カラムがある場合
        ])->save();

        $this->browse(function (Browser $b) use ($buyer, $product) {
            $b->visit(self::BASE . self::LOGIN)
              ->type('email', $buyer->email)
              ->type('password', 'password')
              ->press('ログイン')
              ->visit(self::BASE . self::SHOW_PATH . $product->id)
              ->waitFor('#main-product-image', self::WAIT)
              ->waitFor('.sold-label', 5); // SOLD バッジの出現を待つ

            // SOLD バッジが表示される
            $b->assertPresent('.sold-label')->assertSee('SOLD');

            // 購入ボタンは非表示
            $this->assertNull($b->element('a.buy-button'), '売り切れでも購入ボタンが表示されています');
        });
    }
}
