<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('いいね追加と解除ができる', function () {
    $user = User::factory()->create();
    $p = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('products.like', ['product' => $p->id]))
        ->assertStatus(302); // 実装に合わせて変更可

    $this->actingAs($user)
        ->delete(route('products.unlike', ['product' => $p->id]))
        ->assertStatus(302);
});
