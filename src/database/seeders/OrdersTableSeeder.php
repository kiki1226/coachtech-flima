<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'kogoro@gmail.com')->first();
        $product = Product::first(); // 適当な商品

        Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}
