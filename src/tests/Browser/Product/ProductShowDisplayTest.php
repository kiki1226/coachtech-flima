<?php

namespace Tests\Browser\Product;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductShowDisplayTest extends DuskTestCase
{

    private const BASE      = 'http://nginx';
    private const LOGIN     = '/login';
    private const SHOW_PATH = '/item/'; // 正式URLが /products/{id} なら '/products/' に変更
    private const WAIT      = 12;

    public function test_product_detail_page_shows_required_information(): void
    {
        // 画像（src が通れば中身はダミーでOK）
        $publicImg = public_path('storage/uploads/products/test-detail.jpg');
        if (!File::exists(dirname($publicImg))) File::makeDirectory(dirname($publicImg), 0777, true);
        if (!File::exists($publicImg)) File::put($publicImg, 'dummy');

        // 出品者・カテゴリ・商品
        $seller = User::factory()->create();
        $catA   = Category::factory()->create(['name' => '時計']);
        $catB   = Category::factory()->create(['name' => 'ファッション']);

        $product = Product::factory()->create([
            'user_id'     => $seller->id,
            'name'        => 'テスト腕時計（詳細表示用）',
            'brand'       => null,  // null だとビューは「ブランド名」を表示
            'price'       => 15000,
            'description' => 'スタイリッシュなテスト商品です（詳細ページ検証）。',
            'condition'   => '良好',
            'image_path'  => 'storage/uploads/products/test-detail.jpg',
            'is_sold'     => false,
        ]);
        $product->categories()->attach([$catA->id, $catB->id]);

        // 閲覧ユーザー
        $viewer = User::factory()->create([
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
        ]);

        $this->browse(function (Browser $b) use ($viewer, $product, $catA, $catB) {
            // ログイン → 詳細ページ
            $b->visit(self::BASE . self::LOGIN)
              ->type('email', $viewer->email)
              ->type('password', 'password')
              ->press('ログイン')
              ->visit(self::BASE . self::SHOW_PATH . $product->id)
              ->waitFor('#main-product-image', self::WAIT);

            // 画像（id="main-product-image"）
            $src = $b->attribute('#main-product-image', 'src');
            $this->assertNotEmpty($src, 'メイン画像の src が空です');
            $this->assertStringContainsString('uploads/products/test-detail.jpg', $src);

            // パンくずに商品名、タイトルに商品名
            $b->assertSee('テスト腕時計（詳細表示用）');

            // ブランド（null の場合はデフォルト文言「ブランド名」）
            $b->assertSee('ブランド名');

            // 価格（整形表記を許容：¥15,000 / 15,000円 / 15000 など）
            $priceText = $b->text('.product-price');
            $this->assertMatchesRegularExpression('/¥?\s*15,?000(?:円)?/u', $priceText);

            // 商品説明
            $b->assertSee('スタイリッシュなテスト商品です（詳細ページ検証）。');

            // カテゴリ（複数タグ）
            $b->assertSee($catA->name)->assertSee($catB->name);

            // 状態
            $b->assertSee('良好');

            // 未売却なら購入ボタン表示
            $this->assertNotNull($b->element('a.buy-button'), '購入ボタンが見つかりません');

            // いいね数/コメント数（withCount無でも ?? 0 なので0OK）
            $likeCountText = $b->text('.product-icons .icon-count');
            $this->assertMatchesRegularExpression('/^\d+$/', trim($likeCountText));
            $b->assertSee('コメント (0)'); // 生成していないので0
        });
    }
}
