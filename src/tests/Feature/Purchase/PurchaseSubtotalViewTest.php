<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;

class PurchaseSubtotalViewTest extends TestCase
{
    use RefreshDatabase;

    /** 小計画面に支払い方法セレクトと要約セルがあり、初期表示が未選択である */
    public function test_subtotal_section_has_controls_and_initial_state_is_unselected(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create();
        $product = Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => null,
            'price'    => 1234,
            'name'     => '購入テスト商品',
        ]);

        $res = $this->actingAs($buyer)->get(route('products.purchase', ['product' => $product->id]));
        $res->assertOk();

        // セレクト（#payment_method）がある
        $res->assertSee('id="payment_method"', false);

        // 小計の表示セル（.payment-summary）があり、初期表示は「未選択」
        $res->assertSee('class="payment-summary"', false);
        $res->assertSeeText('未選択');
    }
}
