<?php

namespace App\Exceptions;

use Exception;
use App\Models\Product;

class InsufficientStockException extends Exception
{
    protected $product;

    public function __construct(Product $product, $message = null)
    {
        $this->product = $product;
        $message = $message ?: 'Insufficient stock for product: ' . $product->name;
        parent::__construct($message);
    }

    public function getProduct()
    {
        return $this->product;
    }
}