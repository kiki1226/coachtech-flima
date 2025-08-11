<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 汎用の住所更新は元画面へ戻り住所が書き換わる()
    {
        $user = User::factory()->create([
            'zipcode' => '000-0000',
            'address' => '旧住所',
            'building'=> null,
        ]);

        $this->actingAs($user);

        // 常に address.update を叩く
        $res = $this->from('/dummy-prev') // back() の戻り先（なくてもOKだが明示しておく）
                    ->put(route('address.update', ['id' => $user->id]), [
                        'zipcode'  => '123-4567',
                        'address'  => '新住所',
                        'building' => '新ビル',
                        // product_id は送らない → back() で戻る想定
                    ]);

        $res->assertSessionHasNoErrors();
        $res->assertRedirect('/dummy-prev');  // back() が効いていること

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'zipcode'  => '123-4567',
            'address'  => '新住所',
            'building' => '新ビル',
        ]);
    }

    /** @test */
    public function 購入フローの住所更新は購入ページへリダイレクトする()
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);

        // ここでも address.update を叩くが、product_id を渡すと購入画面に飛ぶ実装
        $res = $this->put(route('address.update', ['id' => $user->id]), [
            'zipcode'    => '987-6543',
            'address'    => '購入時住所',
            'building'   => '購入ビル',
            'product_id' => $product->id,   // ← これがあると products.purchase にリダイレクト
        ]);

        $res->assertSessionHasNoErrors();
        $res->assertRedirect(route('products.purchase', ['product' => $product->id]));

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'zipcode'  => '987-6543',
            'address'  => '購入時住所',
            'building' => '購入ビル',
        ]);
    }
}
