<?php

namespace Tests\Feature\Like;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeToggleTest extends TestCase
{
    use RefreshDatabase;

    private function toggle(User $user, int $productId)
    {
        return $this->actingAs($user)
            ->post(route('products.like', $productId));
    }

    public function test_user_can_like_a_product(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->toggle($buyer, $product->id)->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id'    => $buyer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_unlike_a_product(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->toggle($buyer, $product->id)->assertRedirect(); // like
        $this->assertDatabaseHas('likes', ['user_id'=>$buyer->id,'product_id'=>$product->id]);

        $this->toggle($buyer, $product->id)->assertRedirect(); // unlike
        $this->assertDatabaseMissing('likes', ['user_id'=>$buyer->id,'product_id'=>$product->id]);
    }

    public function test_guest_is_redirected_to_login_when_trying_to_like(): void
    {
        $seller  = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->post(route('products.like', $product->id))
             ->assertRedirect(route('login'));
    }

    public function test_like_on_nonexistent_product_returns_404(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
             ->post(route('products.like', 999999))
             ->assertNotFound();
    }

    public function test_repeated_toggle_flips_state_each_time(): void
    {
        $seller  = User::factory()->create();
        $buyer   = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        foreach ([1,2,3,4,5] as $i) {
            $this->toggle($buyer, $product->id)->assertRedirect();
            $assert = ($i % 2 === 1) ? 'assertDatabaseHas' : 'assertDatabaseMissing';
            $this->$assert('likes', ['user_id'=>$buyer->id,'product_id'=>$product->id]);
        }
    }

    public function test_user_cannot_like_own_product(): void
    {
        $seller  = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->toggle($seller, $product->id)->assertRedirect();

        $this->assertDatabaseMissing('likes', [
            'user_id'    => $seller->id,
            'product_id' => $product->id,
        ]);
    }
}
