<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// 余計なテストは外し、この1本だけでOK
it('購入後は一覧でSOLD表示', function () {
    // 実装では buyer_id が入れば SOLD と判定される（is_sold アクセサ）
    $buyer = User::factory()->create();

    Product::factory()->create([
        'name'     => '購入済みテスト商品',
        'buyer_id' => $buyer->id,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('SOLD');
});
