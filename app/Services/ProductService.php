<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Interfaces\ProductImageRepositoryInterface;
use Illuminate\Support\Facades\DB;;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepositoryInterface $productRepo;
    protected $productImageRepository;
    protected ImageService $imageService;

    public function __construct(
        ProductRepositoryInterface $productRepo,
        ProductImageRepositoryInterface $productImageRepository,
        ImageService $imageService
    ) {
        $this->productRepo = $productRepo;

        $this->productImageRepository = $productImageRepository;
        $this->imageService = $imageService;
    }

    public function list($perPage = 10)
    {
        return Cache::remember('products_page_' . $perPage, 60 * 15, function () use ($perPage) {
            return $this->productRepo->allPaginated($perPage);
        });
    }

    public function create(array $data)
    {

        $data['slug'] = Str::slug($data['name']);
        $product = $this->productRepo->create($data);
        if (isset($data['images']) && is_array($data['images'])) {
            $this->handleProductImages($product, $data['images']);
        }

        $this->clearProductCache();

        return $product;
    }

    public function update(Product $product, array $data)
    {
        DB::beginTransaction();
        try {
            // Update product details
            $product = $this->productRepo->update($product, [
                'name' => $data['name'] ?? $product->name,
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity
            ]);

            if (isset($data['images']) && is_array($data['images'])) {
                $this->handleProductImages($product, $data['images'], true);
            }

            $this->clearProductCache();
            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Product $product)
    {
        DB::beginTransaction();
        try {
            foreach ($product->images as $image) {
                $this->imageService->deleteImage($image->image_path);
                $this->productImageRepository->delete($image);
            }
            $this->productRepo->delete($product);
            $this->clearProductCache();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getById(int $id)
    {
        return Cache::remember('product_' . $id, 60 * 15, function () use ($id) {
            return $this->productRepo->findById($id);
        });
    }

    public function filterByCategory(int $categoryId)
    {
        return Cache::remember('products_category_' . $categoryId, 60 * 15, function () use ($categoryId) {
            return $this->productRepo->filterByCategory($categoryId);
        });
    }

    private function clearProductCache()
    {
        Cache::flush();
    }

    public function getBySlug(string $slug): ?Product
    {
        return $this->productRepo->findBySlug($slug);
    }

    protected function handleProductImages(Product $product, array $images, bool $replaceExisting = false)
    {
        if ($replaceExisting) {
            foreach ($product->images as $image) {
                $this->imageService->deleteImage($image->image_path);
                $this->productImageRepository->delete($image);
            }
        }

        foreach ($images as $index => $base64Image) {
            $imagePath = $this->imageService->saveBase64Image($base64Image, 'products');

            $imageData = [
                'product_id' => $product->id,
                'image_path' => $imagePath,
                'display_order' => $index,
                'is_primary' => ($index === 0) // First image is primary
            ];

            $this->productImageRepository->create($imageData);
        }
    }
}
