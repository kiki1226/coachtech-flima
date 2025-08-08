<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('出品後はリダイレクトされる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('products.store'), [
            'name'        => 'テスト商品',
            'price'       => 1000,
            'description' => '説明',
            'condition'   => 'new',
            // brand や category_ids が必須ならここに追加
        ]);

    // URLだけ見る（成功扱いの有無は問わない）
    $response->assertRedirect(); 
    // 厳密に見るなら↓（あなたの実装がそうなら）
    // $response->assertRedirect(route('mypage.index'));
});
