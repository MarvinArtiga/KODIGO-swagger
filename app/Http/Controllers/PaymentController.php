<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Stripe\PaymentIntent;
use Stripe\Stripe;

/**
 * @OA\Tag(name="Payments")
 */
class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/payments/{order}",
     *     tags={"Payments"},
     *     summary="Create a Stripe payment intent for an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Payment intent created successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Order is not pending payment")
     * )
     */
    public function store(Order $order): JsonResponse
    {
        if ($order->user_id !== auth('api')->id()) {
            return $this->errorResponse('Forbidden', 403);
        }

        if ($order->status !== 'pending') {
            return $this->errorResponse('This order is not pending payment', 422);
        }

        $secret = config('services.stripe.secret');

        if (empty($secret)) {
            return $this->errorResponse('Stripe is not configured', 500);
        }

        Stripe::setApiKey($secret);

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) round($order->total * 100),
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => $paymentIntent->id,
            'amount' => $order->total,
            'currency' => 'usd',
            'status' => 'pending',
        ]);

        return $this->successResponse([
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $order->total,
            'currency' => 'usd',
            'status' => 'pending',
        ], 'Payment intent created successfully', 201);
    }
}
