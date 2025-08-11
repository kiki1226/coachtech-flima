<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 先に存在するレコードを確認して取得
        $user = \App\Models\User::first();
        $product = \App\Models\Product::first();
    
        if ($user && $product) {
            \App\Models\Comment::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'comment' => 'すごく使いやすかったです！',
            ]);
        }
    }
}
