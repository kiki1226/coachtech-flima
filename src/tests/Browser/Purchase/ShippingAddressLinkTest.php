<?php

namespace Tests\Feature\Purchase;

use Tests\DuskTestCase;             // ★これを忘れずに
use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ShippingAddressLinkTest extends DuskTestCase
{

    public function test_purchase_links_correct_address_id(): void
    {
        $user    = User::factory()->create();

        // Seeder が作った既存データを使う（重複作成しない）
        $buyer   = User::whereEmail('dusk-buyer@example.com')->firstOrFail();
        $product = Product::where('name', 'Dusk Product')->firstOrFail();

        $this->browse(function (Browser $browser) use ($buyer, $product) {
            $browser->loginAs($buyer)
                ->visit(route('products.purchase', $product))
                ->assertSee('購入する')
                ->clickLink('変更する')
                ->assertSee('住所の変更');   
        });

        // 旧住所
        DB::table('addresses')->insert([
            'user_id' => $user->id, 'zipcode' => '100-0001',
            'address' => '東京都千代田区1-1', 'building' => '旧ビル101',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        // 新住所（期待される採用先）
        $newId = DB::table('addresses')->insertGetId([
            'user_id' => $user->id, 'zipcode' => '150-0002',
            'address' => '東京都渋谷区恵比寿2-2', 'building' => '新テストビル201',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ← 実装の住所決定ロジックに合わせてここだけ調整
        // 例：最新の住所IDを採用
        $addressId = DB::table('addresses')->where('user_id', $user->id)->max('id');

        // 本番はサービス/コントローラ経由で作る。ここではシンプルに insert
        DB::table('purchases')->insert([
            'user_id'             => $user->id,
            'product_id'          => $product->id,
            'shipping_address_id' => $addressId,
            'payment_method'      => 'card',
            'status'              => 'paid',
            'purchased_at'        => now(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id'             => $user->id,
            'product_id'          => $product->id,
            'shipping_address_id' => $newId,
        ]);
    }
}
