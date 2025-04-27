<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    protected OrderRepositoryInterface $orderRepo;

    public function __construct(OrderRepositoryInterface $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function createOrder(array $data, array $items, int $userId): Order
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $totalAmount = 0;
            foreach ($items as $item) {
                $product = DB::table(table: 'products')->where('id', $item['product_id'])->first();
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    throw new InsufficientStockException($product);
                }
                $totalAmount += $product->price * $item['quantity'];
            }

            $orderData = array_merge($data, [
                'user_id' => $userId,
                'total_amount' => $totalAmount
            ]);
            
            $order = $this->orderRepo->create($orderData);
            
            $this->orderRepo->addOrderItems($order, $items);
            
            return $order;
        });
    }

    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->orderRepo->getUserOrders($userId, $perPage);
    }

    public function getAllOrders(int $perPage = 10): LengthAwarePaginator
    {
        return $this->orderRepo->getAllOrders($perPage);
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepo->findById($id);
    }
}