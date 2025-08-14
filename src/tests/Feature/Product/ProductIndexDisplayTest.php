<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** 1) ゲストは全商品を取得できる（名前で確認） */
    public function test_guest_sees_all_products(): void
    {
        $seller = User::factory()->create();

        $a = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '商品A']);
        $b = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '商品B']);
        $c = Product::factory()->create(['user_id' => $seller->id, 'buyer_id' => null, 'name' => '商品C']);

        $res = $this->get(route('products.index'));
        $res->assertStatus(200);

        // 画面に3商品とも表示されることを確認
        $res->assertSeeText($a->name);
        $res->assertSeeText($b->name);
        $res->assertSeeText($c->name);

        // ビューに渡った products に上記IDが含まれること（件数は縛らない）
        $res->assertViewHas('products', function ($products) use ($a, $b, $c) {
            $ids = $products->pluck('id')->all();
            return in_array($a->id, $ids) && in_array($b->id, $ids) && in_array($c->id, $ids);
        });
    }

    /** 2) 購入済み商品は SOLD 表示（そのまま） */
    public function test_purchased_products_show_sold_badge(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => $buyer->id,
            'name'     => '購入済みA',
        ]);
        Product::factory()->create([
            'user_id'  => $seller->id,
            'buyer_id' => null,
            'name'     => '未購入B',
        ]);

        $res = $this->get(route('products.index'));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertTrue(
            str_contains($html, 'SOLD') || str_contains($html, 'Sold'),
            '購入済み商品のSOLD表示が見つかりません。'
        );
    }

    /** 3) ログイン中は自分の出品が表示されない（非表示/表示の両方で確認） */
    public function test_authenticated_user_does_not_see_own_products(): void
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();

        $own   = Product::factory()->create(['user_id' => $me->id,    'buyer_id' => null, 'name' => '自分の商品']);
        $oth1  = Product::factory()->create(['user_id' => $other->id, 'buyer_id' => null, 'name' => '他人の商品1']);
        $oth2  = Product::factory()->create(['user_id' => $other->id, 'buyer_id' => null, 'name' => '他人の商品2']);

        $this->actingAs($me);

        $res = $this->get(route('products.index'));
        $res->assertStatus(200);

        // 画面の表示確認
        $res->assertDontSeeText($own->name);
        $res->assertSeeText($oth1->name);
        $res->assertSeeText($oth2->name);

        // ビュー変数でも自分の商品が含まれないことを確認（件数は縛らない）
        $res->assertViewHas('products', function ($products) use ($me) {
            return $products->every(fn ($p) => $p->user_id !== $me->id);
        });
    }
}
