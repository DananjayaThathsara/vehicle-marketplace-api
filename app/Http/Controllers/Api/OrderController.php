<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Get user's orders (buyer or seller)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->type === 'buyer') {
            $orders = $this->orderService->getBuyerOrders($user->id);
        } else {
            $orders = $this->orderService->getSellerOrders($user->id);
        }

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get single order
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrder($id);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    /**
     * Create order (purchase vehicle)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $validated['buyer_id'] = $request->user()->id;

        try {
            $order = $this->orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process payment
     */
    public function payment(Request $request, $id)
    {
        $validated = $request->validate([
            'method' => 'required|in:credit_card,bank_transfer,cash',
        ]);

        try {
            $order = $this->orderService->processPayment($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason');

        try {
            $order = $this->orderService->cancelOrder($id, $reason);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
