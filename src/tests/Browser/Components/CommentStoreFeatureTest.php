<?php

namespace Tests\Feature\Comment;

use App\Http\Middleware\VerifyCsrfToken;   // 追加
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class); // ★CSRFだけ無効化
    }

    public function test_user_can_post_comment_and_it_is_saved(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($buyer);

        $res = $this->post(route('comments.store', ['product' => $product->id]), [
            'comment' => 'いいね！',
        ]);

        $res->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'product_id' => $product->id,
            'user_id'    => $buyer->id,
            'comment'    => 'いいね！',
        ]);
    }

    public function test_comment_validation_error_when_empty(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($buyer);

        $res = $this->post(route('comments.store', ['product' => $product->id]), [
            'comment' => '',
        ]);

        $res->assertStatus(302)->assertSessionHasErrors(['comment']);

        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'user_id'    => $buyer->id,
        ]);
    }
}
