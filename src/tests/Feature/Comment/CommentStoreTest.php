<?php

namespace Tests\Feature\Comment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CommentStoreTest extends TestCase
{
    use RefreshDatabase;

    /** 1) ログイン済みのユーザーはコメントを送信できる */
    public function test_authenticated_user_can_post_comment(): void
    {
        $user    = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create();

        $payload = [
            // 実装が body / content / comment のどれでも通るように揃えて送る
            'body'    => 'テストコメント',
            'content' => 'テストコメント',
            'comment' => 'テストコメント',
        ];

        $res = $this->actingAs($user)
            ->post(route('comments.store', ['product' => $product->id]), $payload);

        // 成功はリダイレクト想定（back など）
        $res->assertStatus(302);
        $res->assertSessionHasNoErrors();

        // 保存されていることを緩やかに確認
        $this->assertTrue(
            DB::table('comments')
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->exists(),
            'comments テーブルにレコードが作成されていません。'
        );
    }

    /** 2) ログイン前のユーザーはコメントを送信できない（/loginへ） */
    public function test_guest_cannot_post_comment(): void
    {
        $product = Product::factory()->create();

        $res = $this->post(
            route('comments.store', ['product' => $product->id]),
            [
                'body'    => 'ゲストコメント',
                'content' => 'ゲストコメント',
                'comment' => 'ゲストコメント',
            ]
        );

        $res->assertRedirect('/login');

        $this->assertFalse(
            DB::table('comments')
                ->where('product_id', $product->id)
                ->exists(),
            'ゲスト投稿なのにコメントが作成されています。'
        );
    }

    /** 3) コメント未入力ならバリデーションエラー */
    public function test_validation_error_when_body_is_empty(): void
    {
        $user    = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create();

        $payload = [
            'body'    => '',
            'content' => '',
            'comment' => '',
        ];

        $res = $this->actingAs($user)
            ->post(route('comments.store', ['product' => $product->id]), $payload);

        $res->assertStatus(302)->assertSessionHasErrors(); // キー名は実装依存
        $this->assertFalse(
            DB::table('comments')
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->exists(),
            '未入力なのにコメントが作成されています。'
        );
    }

    /** 4) 255字超ならバリデーションエラー（256字で検証） */
    public function test_validation_error_when_body_exceeds_255_chars(): void
    {
        $user    = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create();

        $tooLong = str_repeat('あ', 256);

        $payload = [
            'body'    => $tooLong,
            'content' => $tooLong,
            'comment' => $tooLong,
        ];

        $res = $this->actingAs($user)
            ->post(route('comments.store', ['product' => $product->id]), $payload);

        $res->assertStatus(302)->assertSessionHasErrors();
        $this->assertFalse(
            DB::table('comments')
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->exists(),
            '255字超なのにコメントが作成されています。'
        );
    }
}
