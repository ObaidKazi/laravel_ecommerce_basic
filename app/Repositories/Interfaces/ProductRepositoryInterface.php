<?php
namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product;

interface ProductRepositoryInterface
{
    public function allPaginated(int $perPage): LengthAwarePaginator;
    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;
    public function delete(Product $product): void;
    public function findById(int $id): ?Product;
    public function filterByCategory(int $categoryId): LengthAwarePaginator;
    public function findBySlug(string $slug): ?Product;
}
