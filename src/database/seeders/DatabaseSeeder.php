<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ① カテゴリー登録
        $this->call(CategoriesTableSeeder::class);

        // ② 商品・ユーザー登録
        $this->call(ProductsTableSeeder::class);

        // ③ カテゴリー商品紐付け
        $this->call(CategoryProductTableSeeder::class);
    }

}
