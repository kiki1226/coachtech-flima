<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('コメントは未入力でバリデーション', function () {
    $user = User::factory()->create();
    $p    = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('comments.store', ['id' => $p->id]), [])
        ->assertSessionHasErrors(['comment']);   // ← フィールド名を comment に
});

it('コメントを投稿できる', function () {
    $user = User::factory()->create();
    $p    = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('comments.store', ['id' => $p->id]), ['comment' => '良い']) // ← ここも comment
        ->assertRedirect();

    $this->get("/item/{$p->id}")
        ->assertOk()
        ->assertSee('良い');
});
