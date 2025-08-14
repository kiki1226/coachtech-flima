<?php

namespace Tests\Browser\Purchase;

use App\Models\Product;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AddressEditSuccessTest extends DuskTestCase
{
    /**
     * 住所を更新すると購入ページに戻り、画面にもDBにも反映されていること。
     */
    public function test_updates_address_and_redirects_to_purchase(): void
    {
        // seeder で作ったユーザー / 商品を使う（DatabaseSeeder の dusk 用データ）
        $buyer   = User::where('email', 'dusk-buyer@example.com')->firstOrFail();
        $product = Product::where('name', 'Dusk Product')->firstOrFail();

        $newZip      = '150-0002';
        $newAddress  = '東京都渋谷区恵比寿2-2';
        $newBuilding = 'テストビル201';

        $this->browse(function (Browser $browser) use ($buyer, $product, $newZip, $newAddress, $newBuilding) {
            $browser->loginAs($buyer)
                // 住所編集ページへ
                ->visit(route('purchase.address.edit', ['product' => $product->id]))
                ->waitFor('@purchase-form', 10)

                // 入力して送信
                ->type('@zipcode',  $newZip)
                ->type('@address',  $newAddress)
                ->type('@building', $newBuilding)
                ->press('@purchase-submit')

                // リダイレクト先（購入ページ）で「購入する」ボタンが見えるまで待つ
                // もしくは dusk="purchase-page" を付けて waitFor('@purchase-page') でもOK
                ->waitForText('購入する', 10)

                // 商品名と新しい住所が画面に出ていることを確認
                ->assertSee('Dusk Product')
                ->assertSee($newZip)
                ->assertSee($newAddress)
                ->assertSee($newBuilding);
        });

        // DB も更新されていること
        $this->assertDatabaseHas('users', [
            'id'       => $buyer->id,
            'zipcode'  => $newZip,
            'address'  => $newAddress,
            'building' => $newBuilding,
        ]);
    }
}
