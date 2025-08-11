<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Category;

uses(TestCase::class, RefreshDatabase::class);

it('商品画像をアップロードできる', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'ダミー']);

    // GD不要：fixturesの実ファイルを使う
    $fixture = base_path('database/seeders/fixtures/products/Clock.jpg');
    $file1 = new UploadedFile($fixture, 'a.jpg', 'image/jpeg', null, true);
    $file2 = new UploadedFile($fixture, 'b.jpg', 'image/jpeg', null, true);

    $this->actingAs($user)->post(route('products.store'), [
        'name'         => 'アップロード検証',
        'price'        => 1000,
        'description'  => 'd',
        'condition'    => '良好',
        'category_ids' => [$category->id],
        'images'       => [$file1, $file2],
    ])->assertStatus(302);

    // ハッシュ名なので「件数」で検証
    expect(Storage::disk('public')->files('uploads/products'))->toHaveCount(2);
});
