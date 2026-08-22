<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(name="Orders")
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="List authenticated user orders",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Orders retrieved successfully")
     * )
     */
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', auth('api')->id())
            ->with('orderItems.product')
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse($orders, 'Orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Show an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order retrieved successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth('api')->id()) {
            return $this->errorResponse('Forbidden', 403);
        }

        return $this->successResponse(
            $order->load('orderItems.product'),
            'Order retrieved successfully',
        );
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_id","quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order created successfully")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $items = $request->validated()['items'];
        $user = auth('api')->user();

        $order = DB::transaction(function () use ($user, $items) {
            $total = 0.0;
            $preparedItems = [];

            foreach ($items as $index => $item) {
                $product = Product::query()->lockForUpdate()->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => ['The selected product does not exist.'],
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => ['The requested quantity exceeds the available stock.'],
                    ]);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($preparedItems as $preparedItem) {
                $order->orderItems()->create([
                    'product_id' => $preparedItem['product_id'],
                    'quantity' => $preparedItem['quantity'],
                    'price' => $preparedItem['price'],
                    'subtotal' => $preparedItem['subtotal'],
                ]);

                $product = Product::query()->findOrFail($preparedItem['product_id']);
                $product->decrement('stock', $preparedItem['quantity']);
            }

            return $order->load('orderItems.product');
        });

        return $this->successResponse($order, 'Order created successfully', 201);
    }
}
