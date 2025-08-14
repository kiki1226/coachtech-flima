<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductShowInfoTest extends TestCase
{
    use RefreshDatabase;

    /** 1) 必要な情報が表示される */
    public function test_product_detail_shows_required_information(): void
    {
        // 商品本体
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'user_id'    => $seller->id,
            'name'       => 'ハーフパンツ',
            'brand'      => 'ナイキ',
            'price'      => 2000,
            'condition'  => '新品',
            'description'=> '涼しくて動きやすいパンツです。',
            'buyer_id'   => null,
        ]);

        // カテゴリ（複数）
        $cat1 = Category::factory()->create(['name' => 'メンズ']);
        $cat2 = Category::factory()->create(['name' => 'パンツ']);
        $product->categories()->attach([$cat1->id, $cat2->id]);

        // いいね 2件（pivot 直書き）
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        DB::table('likes')->insert([
            ['user_id' => $u1->id, 'product_id' => $product->id],
            ['user_id' => $u2->id, 'product_id' => $product->id],
        ]);

        // コメント 2件
        $c1 = Comment::factory()->create([
            'user_id'    => $u1->id,
            'product_id' => $product->id,
            'comment'    => 'とても良さそう！',
        ]);
        $c2 = Comment::factory()->create([
            'user_id'    => $u2->id,
            'product_id' => $product->id,
            'comment'    => '色違いも欲しいです。',
        ]);

        // 表示
        $res = $this->get(route('products.show', ['item_id' => $product->id]));
        $res->assertStatus(200);

        // 画像（altテキスト）、商品名/ブランド/価格
        $res->assertSee('商品画像');
        $res->assertSeeText('ハーフパンツ');
        $res->assertSeeText('ナイキ');
        $res->assertSeeText('¥2,000');

        // 商品説明、状態、カテゴリ、コメント数
        $res->assertSeeText('涼しくて動きやすいパンツです。');
        $res->assertSeeText('商品の状態：新品');
        $res->assertSeeText('メンズ');
        $res->assertSeeText('パンツ');
        $res->assertSeeText('コメント (2)');

        // いいね数（span.icon-count が 2 を表示している想定）
        // コントローラで withCount(['likes','comments']) していれば 2 が出ます
        $this->assertStringContainsString('class="icon-count">2<', $res->getContent());

        // コメントのユーザー名と内容
        $res->assertSeeText($c1->user->name);
        $res->assertSeeText($c2->user->name);
        $res->assertSeeText('とても良さそう！');
        $res->assertSeeText('色違いも欲しいです。');
    }

    /** 2) 複数カテゴリが表示される */
    public function test_multiple_selected_categories_are_displayed(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $seller->id, 'name' => 'カテゴリ検証']);

        $c1 = Category::factory()->create(['name' => '家電']);
        $c2 = Category::factory()->create(['name' => 'アウトドア']);
        $c3 = Category::factory()->create(['name' => 'セール']);

        $product->categories()->attach([$c1->id, $c2->id, $c3->id]);

        $res = $this->get(route('products.show', ['item_id' => $product->id]));
        $res->assertStatus(200);
        $res->assertSeeText('家電');
        $res->assertSeeText('アウトドア');
        $res->assertSeeText('セール');
    }
}
