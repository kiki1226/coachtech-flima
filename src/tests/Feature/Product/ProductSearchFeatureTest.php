<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchFeatureTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    /** 1) 「商品名」で部分一致検索ができる（ゲスト） */
    public function test_guest_can_search_by_partial_name(): void
    {
        $seller = User::factory()->create();

        Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '赤いシャツ']);
        Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '青いパンツ']);
        Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '白い帽子']);

        $res = $this->get(route('products.index', ['keyword' => 'シャツ']));

        $res->assertStatus(200);
        $res->assertSeeText('赤いシャツ');      // 部分一致でヒット
        $res->assertDontSeeText('青いパンツ');   // ヒットしない
        $res->assertDontSeeText('白い帽子');     // ヒットしない
    }

    /**
     * 2) 検索状態がマイリストでも保持される
     *    ＝ キーワード + マイリストの交差で「いいね かつ キーワード一致」のみ表示
     */
    public function test_keyword_is_preserved_on_mylist_and_filters_liked_products(): void
    {
        $me     = User::factory()->create();
        $seller = User::factory()->create();

        $matchLiked    = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'シャツA']);
        $nonMatchLiked = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'パンツB']);
        $notLikedMatch = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'シャツC']);

        // いいね登録
        $this->actingAs($me);
        $this->post(route('products.like', $matchLiked->id));
        $this->post(route('products.like', $nonMatchLiked->id));

        // マイリスト + keyword=シャツ で開く
        $res = $this->get(route('products.index', ['mylist' => 1, 'keyword' => 'シャツ']));

        $res->assertStatus(200);
        $res->assertSeeText('シャツA');         // いいね かつ キーワード一致 → 表示
        $res->assertDontSeeText('パンツB');      // いいね だが 不一致 → 非表示
        $res->assertDontSeeText('シャツC');      // 一致だが いいねしていない → 非表示

        // （任意）タブリンクに keyword が保持されていることを軽く確認
        $this->assertStringContainsString('keyword=%E3%82%B7%E3%83%A3%E3%83%84', $res->getContent());
    }
}

