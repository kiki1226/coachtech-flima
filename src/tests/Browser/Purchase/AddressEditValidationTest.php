<?php

namespace Tests\Browser\Purchase;

use App\Models\Product;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AddressEditValidationTest extends DuskTestCase
{
    

    /** ページに到達でき、必須inputがある（最小） */
    public function test_page_renders_and_inputs_exist()
    {
        $buyer   = User::where('email', 'dusk-buyer@example.com')->firstOrFail();
        $product = Product::where('name', 'Dusk Product')->firstOrFail();

        $this->browse(function (Browser $browser) use ($buyer, $product) {
            $browser->loginAs($buyer) // ★ 先にログイン
                ->visit(route('purchase.address.edit', ['product' => $product->id]))
                ->waitFor('input[name="zipcode"]', 10)
                ->assertPresent('input[name="zipcode"]')
                ->assertPresent('input[name="address"]')
                ->assertPresent('input[name="building"]');
        });
    }

    /** 空送信: 送信しても編集ページに留まる（= バリデーションNG） */
    public function test_empty_submit_stays_on_edit_page(): void
    {
        $user = User::factory()->create([
            'zipcode' => '100-0001',
            'address' => '東京都千代田区1-1',
            'building'=> '旧ビル101',
        ]);
        $product = Product::factory()->create();

        $this->browse(function (Browser $b) use ($user, $product) {
            $editUrl = route('purchase.address.edit', ['product' => $product->id]);

            $b->loginAs($user)
              ->visit($editUrl)
              ->waitFor('input[name="zipcode"]', 10)

              // 初期値を確実に空にして送信
              ->clear('input[name="zipcode"]')
              ->clear('input[name="address"]')
              ->clear('input[name="building"]')
              ->script("
                document.querySelector('input[name=zipcode]').value='';
                document.querySelector('input[name=address]').value='';
                const bd=document.querySelector('input[name=building]'); if(bd){ bd.value=''; }
              ");
            $b->click('button.update-btn');

            // 遷移しないこと（編集ページに留まること）で判定
            $b->pause(300); // ほんの少しだけ待つ
            $b->assertPathIs(parse_url($editUrl, PHP_URL_PATH));
        });
    }

    /** 正常送信: 購入ページへ遷移（= 成功） */
    public function test_valid_input_redirects_to_purchase(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->browse(function (Browser $b) use ($user, $product) {
            $editUrl     = route('purchase.address.edit', ['product' => $product->id]);
            $purchaseUrl = route('products.purchase', ['product' => $product->id]);

            $b->loginAs($user)
              ->visit($editUrl)
              ->waitFor('input[name="zipcode"]', 10)

              // ※ ここはハイフン入りにしておく（1500002 だとルール次第で弾かれる）
              ->clear('input[name="zipcode"]')->type('input[name="zipcode"]', '150-0002')
              ->clear('input[name="address"]')->type('input[name="address"]', '東京都渋谷区2-2')
              ->clear('input[name="building"]')->type('input[name="building"]', 'テストビル201')
              ->click('button.update-btn')

              // 成功は “購入ページに遷移したこと” で判定
              ->waitForLocation($purchaseUrl, 10)
              ->assertPathIs(parse_url($purchaseUrl, PHP_URL_PATH));
        });
    }
}
