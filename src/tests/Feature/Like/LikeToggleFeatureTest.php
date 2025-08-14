<?php

namespace Tests\Feature\Like;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeToggleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_and_unlike_a_product(): void
    {
        $seller = User::factory()->create();
        $user   = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($user);

        // like
        $this->post(route('products.like', $product->id))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('likes', ['user_id' => $user->id, 'product_id' => $product->id]);

        // unlike
        $this->delete(route('products.unlike', $product->id))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_cannot_like_own_product(): void
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller);
        $this->post(route('products.like', $product->id));
        $this->assertDatabaseMissing('likes', ['user_id' => $seller->id, 'product_id' => $product->id]);
    }
}
