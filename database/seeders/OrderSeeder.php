<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Get all users
        $users = User::whereHas('roles',function($query){
            $query->where('name','=', 'customer');
        })->get();
        // Get all products
        $products = Product::all();

        foreach ($users as $user) {
            // Create 1-2 orders per user
            for ($i = 0; $i < rand(1, 2); $i++) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_amount' => 0, // will update after items
                    'status' => 'pending',
                    'payment_method' => 'credit_card',
                    'shipping_address' => '123 Test St, Test City',
                    'billing_address' => '123 Test St, Test City',
                    'notes' => 'Seeded order',
                ]);

                $total = 0;
                // Add 1-3 random products to the order
                $availableProducts = $products->filter(function($product) {
                    return $product->stock_quantity > 0;
                });
                
                // If no products with stock available, skip this order
                if ($availableProducts->isEmpty()) {
                    $order->delete();
                    continue;
                }
                
                $items = $availableProducts->random(min(rand(1, 3), $availableProducts->count()));
                
                foreach ($items as $product) {
                    // Ensure we don't order more than available stock
                    $maxQuantity = min(3, $product->stock_quantity);
                    $quantity = rand(1, $maxQuantity);
                    
                    $subtotal = $product->price * $quantity;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);
                    $total += $subtotal;

                    // Decrease product stock
                    $product->decrement('stock_quantity', $quantity);
                }
                
                // If no items were added, delete the order
                if ($total == 0) {
                    $order->delete();
                    continue;
                }
                
                $order->update(['total_amount' => $total]);
            }
        }
    }
}