<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        $order = Order::create($data);
        return $order;
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'user'])->find($id);
    }

    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllOrders(int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function addOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $product->price * $item['quantity']
            ]);
            
            // Update product inventory
            $product->stock_quantity -= $item['quantity'];
            $product->save();
        }
    }
}