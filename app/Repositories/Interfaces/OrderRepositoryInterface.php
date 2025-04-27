<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function findById(int $id): ?Order;
    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator;
    public function addOrderItems(Order $order, array $items): void;
    public function getAllOrders(int $perPage = 10): LengthAwarePaginator;
}