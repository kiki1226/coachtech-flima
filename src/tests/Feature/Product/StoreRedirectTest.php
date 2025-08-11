<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Product;

uses(TestCase::class, RefreshDatabase::class);

it('出品後にマイページへリダイレクトしレコードが作成される', function () {
    Storage::fake('public');

    $user = \App\Models\User::factory()->create();
    $category = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'ダミー']);

    // 画像は最小1枚（リクエスト次第）。nullable運用なら無くてもOK
    $fixture = base_path('database/seeders/fixtures/products/Clock.jpg');
    $file = new UploadedFile($fixture, 'x.jpg', 'image/jpeg', null, true);

    $res = $this->actingAs($user)->post(route('products.store'), [
        'name'         => '保存検証',
        'price'        => 2000,
        'description'  => 'd',
        'condition'    => '良好',
        'category_ids' => [$category->id],
        'images'       => [$file], // ExhibitionRequestに合わせて調整
    ]);

    $res->assertRedirect(route('mypage.index'));

    $p = Product::latest('id')->first();
    expect($p)->not->toBeNull();
    expect($p->user_id)->toBe($user->id);
    expect($p->image_path)->toStartWith('uploads/products/');
});
