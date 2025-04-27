<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products (paginated, filterable by category)",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
            $categoryId = $request->query('category_id');

            if ($categoryId) {
                $products = $this->productService->filterByCategory($categoryId);
            } else {
                $products = $this->productService->list($perPage);
            }

            return ProductResource::collection($products);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{slug}",
     *     summary="Get product by slug",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function getBySlug($slug)
    {
        try {
            $product = $this->productService->getBySlug($slug);
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            return new ProductResource($product);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","price","image","stock_quantity"},
     *             @OA\Property(property="name", type="string", description="Product name"),
     *             @OA\Property(property="description", type="string", description="Product description"),
     *             @OA\Property(property="price", type="number", format="float", description="Product price"),
     *             @OA\Property(
     *                 property="images", 
     *                 type="array", 
     *                 description="Array of base64 encoded images",
     *                 @OA\Items(
     *                     type="string",
     *                     format="base64",
     *                     description="Base64 encoded image string"
     *                 )
     *             ),
     *             @OA\Property(property="stock_quantity", type="integer", description="Available stock quantity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $product = $this->productService->create($request->validated());
            return new ProductResource($product);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     summary="Update a product",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Product name"),
     *             @OA\Property(property="slug", type="string", description="Product slug"),
     *             @OA\Property(property="description", type="string", description="Product description"),
     *             @OA\Property(property="price", type="number", format="float", description="Product price"),
     *             @OA\Property(
     *                 property="images", 
     *                 type="array", 
     *                 description="Array of base64 encoded images",
     *                 @OA\Items(
     *                     type="string",
     *                     format="base64",
     *                     description="Base64 encoded image string"
     *                 )
     *             ),
     *             @OA\Property(property="stock_quantity", type="integer", description="Available stock quantity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *    )
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $product = $this->productService->update($product, $request->validated());
            return new ProductResource($product);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     summary="Delete a product",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $this->productService->delete($product);
            return response()->json(['message' => 'Deleted']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
