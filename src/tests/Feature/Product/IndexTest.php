<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('ログイン時は自分の出品を除外', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    $mine = Product::factory()->for($me)->create(['name' => '私の品']);
    $otherP = Product::factory()->for($other)->create(['name' => '他人の品']);

    $this->actingAs($me)
        ->get(route('products.index'))
        ->assertOk()
        ->assertDontSee('私の品')
        ->assertSee('他人の品');
});

it('売却済みはSOLD表示', function () {
    $buyer = \App\Models\User::factory()->create();
    \App\Models\Product::factory()->create([
        'buyer_id' => $buyer->id,   // ← ここがポイント
        'name'     => '売れた品',
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('SOLD');
});
