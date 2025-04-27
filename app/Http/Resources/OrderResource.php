<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'notes' => $this->notes,
            'items' => $this->when($this->relationLoaded('items'), function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => new ProductResource($item->product),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}