<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Interfaces\ProductImageRepositoryInterface;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function create(array $data): ProductImage
    {
        return ProductImage::create($data);
    }

    public function update(ProductImage $productImage, array $data): ProductImage
    {
        $productImage->update($data);
        return $productImage;
    }

    public function delete(ProductImage $productImage): bool
    {
        return $productImage->delete();
    }

    public function getByProduct(Product $product)
    {
        return $product->images()->orderBy('display_order')->get();
    }

    public function setPrimary(ProductImage $productImage): bool
    {
        // First, set all images for this product as non-primary
        ProductImage::where('product_id', $productImage->product_id)
            ->update(['is_primary' => false]);
        
        // Then set this image as primary
        return $productImage->update(['is_primary' => true]);
    }
}