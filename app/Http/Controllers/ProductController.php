<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Products")
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="List all products",
     *     @OA\Response(response=200, description="Products retrieved successfully")
     * )
     */
    public function index(): JsonResponse
    {
        return $this->successResponse(
            Product::query()->orderBy('created_at', 'desc')->get(),
            'Products retrieved successfully',
        );
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Show a product",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product retrieved successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(Product $product): JsonResponse
    {
        return $this->successResponse($product, 'Product retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock"},
     *             @OA\Property(property="name", type="string", example="Wireless Mouse"),
     *             @OA\Property(property="description", type="string", example="Ergonomic mouse"),
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *             @OA\Property(property="stock", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Update a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Mouse"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="price", type="number", format="float", example=39.99),
     *             @OA\Property(property="stock", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->fill($request->validated());
        $product->save();

        return $this->successResponse($product, 'Product updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->successResponse([], 'Product deleted successfully');
    }
}
