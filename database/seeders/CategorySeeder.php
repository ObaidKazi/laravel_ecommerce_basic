<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $categories = ['Electronics', 'Books', 'Clothing', 'Home', 'Toys'];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => $faker->sentence(8),
            ]);
        }
    }
}
