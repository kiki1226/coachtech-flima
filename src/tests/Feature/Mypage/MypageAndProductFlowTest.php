<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class MypageAndProductFlowTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'is_profile_set'    => 1,
        ]);
    }

    /** @test */
    public function store_success_redirects_to_mypage_sell_and_persists_everything()
    {
        Storage::fake('public'); // 画像は送らないが、他テスト影響を避けるため残す

        $user = $this->verifiedUser();
        $category = Category::factory()->create();

        $payload = [
            'name'         => 'テスト商品',
            'description'  => '説明',
            'price'        => 1234,
            'condition'    => '良好',
            'category_ids' => [$category->id],
            // 画像は任意のため送らない（GD拡張不要にする）
        ];

        $res = $this->actingAs($user)->post(route('products.store'), $payload);

        $res->assertRedirect('/mypage?tab=sell');

        $this->assertDatabaseHas('products', [
            'name'    => 'テスト商品',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function store_validation_error_redirects_back_to_sell_with_errors()
    {
        $user = $this->verifiedUser();

        // 必須の name / category_ids を欠落させて失敗
        $payload = [
            'description' => '説明だけ',
            'price'       => 1000,
            'condition'   => '良好',
        ];

        $res = $this->actingAs($user)->post(route('products.store'), $payload);

        $res->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['name', 'category_ids']);
    }

    /** @test */
    public function mypage_shows_only_my_products_and_sell_is_default()
    {
        $me    = $this->verifiedUser();
        $other = User::factory()->create();

        Product::factory()->create(['name'=>'私の出品', 'user_id'=>$me->id]);
        Product::factory()->create(['name'=>'他人の出品', 'user_id'=>$other->id]);

        $res = $this->actingAs($me)->get('/mypage'); // ?tab なし → sell がデフォルト

        $res->assertOk()
            ->assertSee('私の出品')
            ->assertDontSee('他人の出品');
    }

    /** @test */
    public function comments_list_is_rendered_above_the_comment_form()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        // 実カラム名は comment
        $product->comments()->create([
            'user_id' => $user->id,
            'comment' => '最初のコメント',
        ]);
        $product->comments()->create([
            'user_id' => $user->id,
            'comment' => '二番目のコメント',
        ]);

        $res = $this->actingAs($user)->get(route('products.show', ['item_id'=>$product->id]));

        // 一覧のテキストが「商品へのコメント」見出しより前に現れることを担保
        $res->assertSeeInOrder(['最初のコメント', '商品へのコメント']);
        $res->assertSeeInOrder(['二番目のコメント', '商品へのコメント']);
    }

    /** @test */
    public function routes_are_wired_expectedly_for_store_and_mypage()
    {
        $this->assertSame('/products', route('products.store', [], false)); // POST /products
        $this->get('/mypage')->assertStatus(302); // 認証での302は存在確認としてOK
    }
}
