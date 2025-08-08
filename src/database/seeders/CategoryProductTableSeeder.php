<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

class CategoryProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category1 = Category::firstOrCreate(['name' => '洋服']);
        $category2 = Category::firstOrCreate(['name' => 'メンズ']);
        $category3 = Category::firstOrCreate(['name' => '家電']);
        $category4 = Category::firstOrCreate(['name' => '食品']);

        $products = Product::all();

        foreach ($products as $product) {
            if ($product->name === '腕時計') {
                $product->categories()->sync([$category1->id, $category2->id]);
            } elseif ($product->name === 'HDD') {
                $product->categories()->sync([$category3->id]);
            } elseif ($product->name === '玉ねぎ３束') {
                $product->categories()->sync([$category4->id]);
            } else {
                $product->categories()->sync([]); // カテゴリなし
            }

            }
    }
}
