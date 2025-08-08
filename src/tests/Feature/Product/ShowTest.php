<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('/item/{item_id} で詳細が見える', function () {
    $p = Product::factory()->create(['name' => 'テスト品']);
    $this->get("/item/{$p->id}")
        ->assertOk()
        ->assertSee('テスト品');
});
