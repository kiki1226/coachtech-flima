<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->words(2, true), // or fake()->words(2, true)
            'brand'       => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'features'    => $this->faker->words(3, true), // 使ってなければ削除可
            'price'       => $this->faker->numberBetween(500, 20000),
            'condition'   => 'new',
            'image_path'  => 'uploads/products/no-image.png',
            'is_sold'     => false,
            'buyer_id'    => null,
        ];
    }
}
