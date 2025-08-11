<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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

        // 画像を public にコピー（任意）
        $this->publishProductImages();
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
