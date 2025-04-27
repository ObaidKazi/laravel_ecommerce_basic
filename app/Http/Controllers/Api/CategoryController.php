<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="slug", type="string", example="electronics"),
     *                     @OA\Property(property="description", type="string", example="Electronic devices and gadgets"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function index()
    {
        try {
            return CategoryResource::collection($this->categoryService->all());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Intenal Server Error'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     tags={"Category"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $category = $this->categoryService->create($request->validated());
            return new CategoryResource($category);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category",
     *     tags={"Category"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated"
     *     ),
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $category = $this->categoryService->find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            $category = $this->categoryService->update($id, $request->validated());
            return new CategoryResource($category);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete a category",
     *     tags={"Category"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted"
     *     ),
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete category with associated products"
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Something went wrong" 
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            if (!Auth::user() || !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            $category = $this->categoryService->find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            if ($category->products()->count() > 0) {
                return response()->json(['message' => 'Cannot delete category with associated products'], 422);
            }
            $this->categoryService->delete($id);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
