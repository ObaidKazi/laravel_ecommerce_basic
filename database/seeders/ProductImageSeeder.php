<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        // Get all products
        $products = Product::all();
        
        // Dummy image URL
        $dummyImageUrl = 'https://www.udgamschool.com/wp-content/uploads/2023/05/dummy-image-grey-e1398449111870.jpg';
        
        foreach ($products as $product) {
            // Add 1-3 images for each product
            $imageCount = rand(1, 3);
            
            for ($i = 0; $i < $imageCount; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $dummyImageUrl,
                ]);
            }
        }
    }
}