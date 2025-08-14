<?php

namespace Tests\Browser\Purchase;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

use App\Models\User;
use App\Models\Product;

class PaymentMethodReflectsInSummaryTest extends DuskTestCase
{
   

    /** セレクト変更が小計の表示に反映される */
    public function test_payment_method_selection_updates_summary(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create();
        $product = Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => null,
            'name'     => 'Dusk支払いテスト商品',
            'price'    => 1234,
        ]);

        $this->browse(function (Browser $browser) use ($buyer, $product) {
            $browser->loginAs($buyer)
                // ルート名で遷移（APP_URL とズレない）
                ->visitRoute('products.purchase', ['product' => $product->id])

                // デバッグ用にソース/スクショを保存（失敗時の調査に便利）
                ->storeSource('purchase-page.html')
                ->screenshot('purchase-page')
                ->tap(function ($b) {
                    // 現在URLを保存
                    $url = $b->script('return window.location.href')[0] ?? 'unknown';
                    file_put_contents(base_path('tests/Browser/source/current-url.txt'), $url.PHP_EOL);
                    // ページタイトルも保存
                    $title = $b->script('return document.title')[0] ?? '';
                    file_put_contents(base_path('tests/Browser/source/current-title.txt'), $title.PHP_EOL);
                })

                // 本当に購入ページに居るか（/loginに飛んでいないか）を確認
                ->assertPathIs("/products/{$product->id}/purchase")
                
                // DOM安定を待つ
                ->waitFor('#payment_method', 5)
                ->waitFor('.payment-summary', 5)

                // 初期表示
                ->assertSeeIn('.payment-summary', '未選択')

                // card → クレジットカード
                ->select('#payment_method', 'card')
                ->waitUsing(5, 100, function () use ($browser) {
                    return trim($browser->text('.payment-summary')) === 'クレジットカード';
                })
                ->assertSeeIn('.payment-summary', 'クレジットカード')

                // konbini → コンビニ払い
                ->select('#payment_method', 'konbini')
                ->waitUsing(5, 100, function () use ($browser) {
                    return trim($browser->text('.payment-summary')) === 'コンビニ払い';
                })
                ->assertSeeIn('.payment-summary', 'コンビニ払い')

                // 空に戻す → 未選択
                ->select('#payment_method', '')
                ->waitUsing(5, 100, function () use ($browser) {
                    return trim($browser->text('.payment-summary')) === '未選択';
                })
                ->assertSeeIn('.payment-summary', '未選択');
        });
    }
}
