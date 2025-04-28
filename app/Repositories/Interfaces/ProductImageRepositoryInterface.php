<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use App\Models\ProductImage;

interface ProductImageRepositoryInterface
{
    public function create(array $data): ProductImage;
    public function update(ProductImage $productImage, array $data): ProductImage;
    public function delete(ProductImage $productImage): bool;
    public function getByProduct(Product $product);
}
