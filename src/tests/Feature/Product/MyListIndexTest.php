<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListIndexTest extends TestCase
{
    use RefreshDatabase;

    /** 1) いいねした商品だけが表示される */
    public function test_only_liked_products_are_listed(): void
    {
        $me = User::factory()->create();
        $seller = User::factory()->create();

        $liked1 = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'いいねA']);
        $liked2 = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'いいねB']);
        $other  = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '未いいねC']);

        $this->actingAs($me);
        $this->post(route('products.like', $liked1->id));
        $this->post(route('products.like', $liked2->id));

        $res = $this->get(route('products.index', ['mylist' => 1]));
        $res->assertStatus(200);

        $res->assertSeeText('いいねA');
        $res->assertSeeText('いいねB');
        $res->assertDontSeeText('未いいねC');
    }

    /** 2) マイリスト内の購入済み商品には SOLD 表示が付く */
    public function test_purchased_liked_products_show_sold_badge(): void
    {
        $me = User::factory()->create();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $soldLiked = Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => $buyer->id,     // 購入済み
            'name'     => '購入済みいいね',
        ]);

        $this->actingAs($me);
        $this->post(route('products.like', $soldLiked->id));

        $res = $this->get(route('products.index', ['mylist' => 1]));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertTrue(str_contains($html, 'SOLD') || str_contains($html, 'Sold'));
    }

    /** 3) 自分が出品した商品は（たとえいいねしても）表示されない */
    public function test_own_products_do_not_appear_even_if_liked(): void
    {
        $me = User::factory()->create();

        $own  = Product::factory()->create(['user_id' => $me->id, 'buyer_id' => null, 'name' => '自分の商品']);
        $oth1 = Product::factory()->create(['user_id' => User::factory()->create()->id, 'buyer_id' => null, 'name' => '他人1']);

        $this->actingAs($me);
        // 自分の商品にいいねしても（仕様として）一覧に出さない
        $this->post(route('products.like', $own->id));
        $this->post(route('products.like', $oth1->id));

        $res = $this->get(route('products.index', ['mylist' => 1]));
        $res->assertStatus(200);

        $res->assertDontSeeText('自分の商品');
        $res->assertSeeText('他人1');
    }

    /** 4) 未認証の場合は（マイリストタブでは）何も表示されない */
    public function test_guest_sees_nothing_on_mylist(): void
    {
        $seller = User::factory()->create();
        Product::factory()->count(2)->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => 'ダミー']);

        $res = $this->get(route('products.index', ['mylist' => 1]));
        $res->assertStatus(200);

        // ビューに渡された products が空であること（実装に合わせてここを調整）
        $res->assertViewHas('products', fn ($products) => $products->isEmpty());
    }
}
