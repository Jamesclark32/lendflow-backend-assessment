<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->numberBetween(1000, 99999),
            'on_sale' => $this->faker->boolean(),
            'sale_price' => $this->faker->numberBetween(1000, 99999),
            'color' => $this->faker->randomElement(['red', 'green', 'blue', 'black', 'white']),
            'upc' => $this->faker->numberBetween(1000, 99999),
        ];
    }
}
