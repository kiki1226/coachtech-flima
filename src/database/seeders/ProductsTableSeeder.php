<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 出品者（あなたのプロジェクトは username を使っている）
        $seller = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'テストユーザー', 'password' => bcrypt('password')]
        );

        $now = now();

        // フィクスチャ（database/seeders/fixtures/products/*.jpg を想定）
        $catalog = [
            ['name'=>'腕時計',     'file'=>'Clock.jpg',      'desc'=>'スタイリッシュなデザインのメンズ腕時計', 'features'=>'特徴：防水・耐久性抜群', 'price'=>15000, 'condition'=>'良好'],
            ['name'=>'HDD',       'file'=>'HardDisk.jpg',   'desc'=>'高速で信頼性の高いハードディスク',     'features'=>'1TB、静音設計、USB3.0対応', 'price'=>5000,  'condition'=>'目立った傷や汚れなし'],
            ['name'=>'玉ねぎ３束', 'file'=>'negi.jpg',       'desc'=>'新鮮な玉ねぎ３束のセット',             'features'=>'北海道産、甘みたっぷり',     'price'=>300,   'condition'=>'やや傷や汚れあり'],
            ['name'=>'革靴',       'file'=>'LeatherShoes.jpg','desc'=>'クラシックなデザインの革靴',         'features'=>'本革、通気性抜群',           'price'=>4000,  'condition'=>'状態が悪い'],
            ['name'=>'ノートPC',   'file'=>'Laptop.jpg',     'desc'=>'高性能なノート型パソコン',             'features'=>'SSD搭載・軽量ボディ',         'price'=>45000, 'condition'=>'良好'],
            ['name'=>'マイク',     'file'=>'micro.jpg',      'desc'=>'高音質のレコーディング用マイク',       'features'=>'ノイズ除去・クリアな音質',     'price'=>8000,  'condition'=>'目立った傷や汚れなし'],
            ['name'=>'ショルダー', 'file'=>'pocket.jpg',     'desc'=>'おしゃれなショルダーバッグ',           'features'=>'防水・大容量',                 'price'=>3500,  'condition'=>'やや傷や汚れあり'],
            ['name'=>'タンブラー', 'file'=>'Tumbler.jpg',    'desc'=>'使いやすいタンブラー',                 'features'=>'保温・保冷機能',               'price'=>500,   'condition'=>'状態が悪い'],
            ['name'=>'コーヒーミル','file'=>'coffeemill.jpg', 'desc'=>'手動のコーヒーミル',                   'features'=>'セラミック刃・粒度調整',       'price'=>4000,  'condition'=>'良好'],
            ['name'=>'メイクセット','file'=>'makeupset.jpg',  'desc'=>'便利なメイクセット',                   'features'=>'持ち運び便利・多機能',         'price'=>2500,  'condition'=>'良好・目立った傷や汚れなし'],
        ];

        $srcDir = database_path('seeders/fixtures/products');
        $items  = [];

        foreach ($catalog as $i => $row) {
            $srcPath = $srcDir.'/'.$row['file'];
            $destRel = 'products/'.$row['file']; // ← DBに保存する相対パス
            // 実ファイルがあれば public ディスクへコピー
            if (File::exists($srcPath)) {
                Storage::disk('public')->put($destRel, File::get($srcPath));
            } else {
                // 無ければ noimage にフォールバック（事前に用意）
                $destRel = 'products/noimage.png';
            }

            $items[] = [
                'user_id'     => $seller->id,
                'name'        => $row['name'],
                'image_path'  => $destRel,                // ← 相対パスのみ
                'description' => $row['desc'],
                'features'    => $row['features'] ?? null,
                'price'       => $row['price'],
                'condition'   => $row['condition'] ?? null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        Product::insert($items);

        // 直近挿入分にカテゴリを付与（任意）
        $products = Product::orderByDesc('id')->take(count($items))->get();
        foreach ($products as $p) {
            $catIds = Category::inRandomOrder()->take(rand(1,3))->pluck('id');
            if ($catIds->isNotEmpty()) {
                $p->categories()->attach($catIds);
            }
        }

        $this->command?->info('Seeded products with images to storage/app/public/products.');
    }
    
}
