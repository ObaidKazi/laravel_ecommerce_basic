<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all category IDs
        $categoryIds = Category::pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            $product = Product::create([
                'name' => $faker->word . ' ' . $faker->word,
                'slug' => $faker->unique()->slug,
                'description' => $faker->sentence(10),
                'price' => $faker->randomFloat(2, 10, 1000),
                'stock_quantity' => $faker->numberBetween(1, 100),
            ]);

            // Attach random categories (1-3) to each product
            $product->categories()->attach($faker->randomElements($categoryIds, rand(1, 3)));
        }
    }
}
