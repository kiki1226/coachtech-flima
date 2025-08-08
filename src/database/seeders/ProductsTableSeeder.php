<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProductsTableSeeder extends Seeder
{
    public function run()
{
    $user = User::create([
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'avatar' => 'uploads/avatars/no-image.png',
    ]);

    DB::table('products')->insert([
            [
                'name' => '腕時計',
                'image_path' => 'uploads/products/Clock.jpg',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'features' => '特徴：防水・耐久性抜群',
                'price' => 15000,
                'user_id' => $user->id,
                'condition' => '良好',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HDD',
                'image_path' => 'uploads/products/HardDisk.jpg',
                'description' => '高速で信頼性の高いハードディスク',
                'features' => '1TB、静音設計、USB3.0対応',
                'price' => 5000,
                'user_id' => $user->id,
                'condition' => '目立った傷や汚れなし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '玉ねぎ３束',
                'image_path' => 'uploads/products/negi.jpg',
                'description' => '新鮮な玉ねぎ３束のセット',
                'features' => '北海道産、甘みたっぷり',
                'price' => 300,
                'user_id' => $user->id,
                'condition' => 'やや傷や汚れあり',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '革靴',
                'image_path' => 'uploads/products/LeatherShoes.jpg',
                'description' => 'クラシックなデザインの革靴',
                'features' => '本革、通気性抜群',
                'price' => 4000,
                'user_id' => $user->id,
                'condition' => '状態が悪い',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ノートPC',
                'image_path' => 'uploads/products/Laptop.jpg',
                'description' => '高性能なノート型パソコン',
                'features' => 'SSD搭載・軽量ボディ',
                'price' => 45000,
                'user_id' => $user->id,
                'condition' => '良好',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'マイク',
                'image_path' => 'uploads/products/micro.jpg',
                'description' => '高音質のレコーディング用マイク',
                'features' => 'ノイズ除去・クリアな音質',
                'price' => 8000,
                'user_id' => $user->id,
                'condition' => '目立った傷や汚れなし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ショルダーバック',
                'image_path' => 'uploads/products/pocket.jpg',
                'description' => 'おしゃれなショルダーバック',
                'features' => '防水・大容量',
                'price' => 3500,
                'user_id' => $user->id,
                'condition' => 'やや傷や汚れあり',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'タンブラー',
                'image_path' => 'uploads/products/Tumbler.jpg',
                'description' => '使いやすいタンブラー',
                'features' => '保温・保冷機能',
                'price' => 500,
                'user_id' => $user->id,
                'condition' => '状態が悪い',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'コーヒーミル',
                'image_path' => 'uploads/products/coffeemill.jpg',
                'description' => '手動のコーヒーミル',
                'features' => 'セラミック刃・粒度調整',
                'price' => 4000,
                'user_id' => $user->id,
                'condition' => '良好',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'メイクセット',
                'image_path' => 'uploads/products/makeupset.jpg',
                'description' => '便利なメイクセット',
                'features' => '持ち運び便利・多機能',
                'price' => 2500,
                'user_id' => $user->id,
                'condition' => '良好・目立った傷や汚れなし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
