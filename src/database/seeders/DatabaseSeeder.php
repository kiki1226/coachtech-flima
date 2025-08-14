<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ProductsTableSeeder::class,
            CategoryProductTableSeeder::class,
        ]);

        // 任意：画像コピー処理は純粋なファイル操作だけで。DuskのAPIを使わない
        $this->publishProductImages();

        if (app()->environment('dusk')) {
            $buyer = User::factory()->create([
                'name' => 'Dusk Buyer',
                'email' => 'dusk-buyer@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_profile_set' => true,
                'zipcode'  => '123-4567',
                'address'  => '東京都千代田区1-1',
                'building' => 'テストビル',
            ]);

            $seller = User::factory()->create([
                'name' => 'Dusk Seller',
                'email' => 'dusk-seller@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_profile_set' => true,
            ]);

            $category = Category::first() ?? Category::factory()->create(['name' => 'テストカテゴリ']);

            $product = Product::factory()
                ->for($seller)
                ->create([
                    'name'   => 'Dusk Product',
                    'is_sold'=> false,
                    'price'  => 15000,
                ]);

            $product->categories()->sync([$category->id]);
        }
    }

    private function publishProductImages(): void
    {
        $src = database_path('seeders/fixtures/products');
        $dst = public_path('uploads/products');

        if (!File::exists($src)) {
            return; // fixtures 無ければスキップ
        }

        try {
            // ローカルだけクリーンに入れ直す（testingでは消さない）
            if (app()->environment('local') && File::exists($dst)) {
                File::deleteDirectory($dst);
            }
            if (!File::exists($dst)) {
                File::makeDirectory($dst, 0755, true);
            }

            File::copyDirectory($src, $dst);
        } catch (\Throwable $e) {
            // 画像コピー失敗でシーディング全体を落とさない
            \Log::warning('[Seeder] image publish skipped: '.$e->getMessage());
        }
    }
}
