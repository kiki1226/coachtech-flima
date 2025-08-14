<?php

namespace Tests\Feature\Comment;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーは商品にコメントを投稿でき、DBに保存される
     */
    public function test_user_can_post_comment_and_it_is_saved(): void
    {
        // 出品者と商品
        $seller  = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        // コメントする購入者
        $buyer = User::factory()->create();

        // ログインして投稿
        $res = $this->actingAs($buyer)->post(
            route('comments.store', $product), // {product} に合わせて Product を渡す
            ['comment' => 'いいね！']
        );

        // バリデーションエラーが無いこと・リダイレクトすること
        $res->assertSessionHasNoErrors();
        $res->assertRedirect();

        // DB に保存されていること
        $this->assertDatabaseHas('comments', [
            'product_id' => $product->id,
            'user_id'    => $buyer->id,
            'comment'    => 'いいね！',
        ]);
    }

    /**
     * 空のコメントはバリデーションに失敗する
     */
    public function test_comment_validation_error_when_empty(): void
    {
        $seller  = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);
        $buyer   = User::factory()->create();

        $res = $this->actingAs($buyer)->post(
            route('comments.store', $product),
            ['comment' => ''] // 空文字
        );

        // comment フィールドでエラー
        $res->assertSessionHasErrors(['comment']);

        // もちろん保存されていない
        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'user_id'    => $buyer->id,
        ]);
    }
}
