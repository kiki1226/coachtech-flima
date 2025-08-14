<?php
// tests/Browser/Purchase/PurchaseGateTest.php
namespace Tests\Browser\Purchase;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\User;
use App\Models\Product;

class PurchaseGateTest extends DuskTestCase
{
    

    /** ゲストは購入ページに入れずログインへ */
    public function test_guest_is_redirected_to_login(): void
    {
        $seller  = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->browse(function (Browser $b) use ($product) {
            $b->visitRoute('products.purchase', ['product' => $product->id])
              ->assertPathIs('/login');
        });
    }
}
