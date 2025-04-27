<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exceptions\InsufficientStockException;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method","shipping_address","billing_address","items"},
     *             @OA\Property(property="payment_method", type="string"),
     *             @OA\Property(property="shipping_address", type="string"),
     *             @OA\Property(property="billing_address", type="string"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Insufficient stock"
     *     )
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->except('items'),
                $request->input('items'),
                Auth::id()
            );
            return new OrderResource($order->load('items.product'));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'product_id' => $e->getProduct()->id,
                'product_name' => $e->getProduct()->name,
                'available_stock' => $e->getProduct()->stock_quantity
            ], 422);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get orders (all for admin, user's orders for regular users)",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                $orders = $this->orderService->getUserOrders(Auth::user()?->id, $perPage);
            } else {
                $orders = $this->orderService->getAllOrders($perPage);
                
            }

            return OrderResource::collection($orders);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details by ID",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrderById($id);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            return new OrderResource($order);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
