<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
class ProductRepository implements ProductRepositoryInterface
{
    public function allPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Cache::remember('products_page_' . request()->get('page', 1) . '_per_' . $perPage, 60 * 5, function () use ($perPage) {
            return Product::with(['categories','images'])->paginate($perPage);
        });
       
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }
        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        $this->clearCache($product->id);
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }
        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['categories','images'])->where('id',$id)->first();
    }
    private function clearCache($productId = null)
    {
        Cache::forget('products_page_' . request()->get('page', 1) . '_per_' . request()->get('per_page', 10));
        
        if ($productId) {
            Cache::forget('product_' . $productId);
        }
        
        if (request()->has('category_id')) {
            Cache::forget('products_category_' . request()->get('category_id') . '_page_' . request()->get('page', 1));
        }
    }


    public function filterByCategory(int $categoryId): LengthAwarePaginator
    {
        return Cache::remember('products_category_' . $categoryId . '_page_' . request()->get('page', 1), 60 * 15, function () use ($categoryId) {
            return Product::whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })->with(['categories','images'])->paginate(10);
        });
    }

    public function findBySlug(string $slug): ?Product
    {
        return Cache::remember('products_slug_' . $slug, 60 * 15, function () use ($slug) {
            return Product::where('slug', $slug)->with(['categories','images'])->first();
        });
    }
}
