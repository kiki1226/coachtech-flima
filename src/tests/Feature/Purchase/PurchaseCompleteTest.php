<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Mockery;
use Stripe\Checkout\Session as StripeSession;

class PurchaseCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1) purchase.complete のダミー（{id?} に注意）
        $router = app('router');
        if (! Route::has('purchase.complete')) {
            $router->middleware('web')
                ->any('/__purchase_complete_dummy/{id?}', fn () => response('ok', 200))
                ->where('id', '.*')
                ->name('purchase.complete');
            $router->getRoutes()->refreshNameLookups();
            $router->getRoutes()->refreshActionLookups();
        }

        // 2) Stripe セッション作成をモック（外部通信させない）
        //    static メソッドなので alias モックを使う
        Mockery::mock('alias:' . StripeSession::class)
            ->shouldReceive('create')
            ->andReturn((object)['url' => '/__dummy_stripe_url']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * ① 「購入する」押下で決済ページへリダイレクトされる（本体はStripe等へ）
     *    ※ buyer_id の更新はここでは期待しない（コールバック側の責務のため）
     */
    public function test_clicking_purchase_button_redirects_to_gateway(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $product = Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => null,
        ]);

        $payload = [
            'product_id'       => $product->id,
            'payment_method'   => 'card',
            'shipping_address' => '東京都千代田区1-1-1',
        ];

        $res = $this->actingAs($buyer)->post(route('purchase.redirect'), $payload);

        // purchase.complete へ 302 で飛べること（ダミールートで受ける）
        $res->assertStatus(302);
    }

    /**
     * ② 購入完了後（＝buyer_id が入った状態）だと
     *     - 一覧に SOLD 表示
     *     - マイページ「購入した商品」タブに載る
     *   ※ 実環境ではコールバックで buyer_id を入れる想定。ここではシミュレート。
     */
    public function test_after_completed_purchase_item_is_sold_and_listed_in_profile(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => null,
            'name'     => '購入対象の商品',
        ]);

        // 購入完了をシミュレート（コールバックでやることを直接反映）
        $product->buyer_id = $buyer->id;
        $product->save();

        // 一覧で SOLD 表示
        $list = $this->get(route('products.index'))->assertOk();
        $html = $list->getContent();
        $this->assertTrue(str_contains($html, 'SOLD') || str_contains($html, 'Sold'));

        // マイページ（購入タブ）に表示
        $mypage = $this->actingAs($buyer)->get('/mypage?tab=buy');
        $mypage->assertOk()->assertSeeText('購入対象の商品');
    }
}
