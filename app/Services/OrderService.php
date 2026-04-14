<?php

namespace App\Services;

use App\Events\OrderEvent;
use App\Models\Order;
use App\Models\Vehicle;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepo,
        private VehicleRepositoryInterface $vehicleRepo,
        private VehicleService $vehicleService
    ) {}

    /**
     * Create new order (Purchase vehicle)
     */
    public function createOrder(array $data)
    {
        DB::beginTransaction();

        try {
            // Get vehicle
            $vehicle = $this->vehicleRepo->find($data['vehicle_id']);

            //Business Rule: Validate vehicle is available
            if ($vehicle->status !== 'listed') {
                throw new \Exception('Vehicle is not available for purchase');
            }

            // 3Business Rule: Can't buy your own vehicle
            if ($vehicle->seller_id === $data['buyer_id']) {
                throw new \Exception('Cannot buy your own vehicle');
            }

            // Calculate order amounts
            $amount = $vehicle->price;
            $tax = round($amount * 0.08, 2); // 8% tax
            $total = $amount + $tax;

            //Create order
            $orderData = [
                'vehicle_id' => $vehicle->id,
                'buyer_id' => $data['buyer_id'],
                'seller_id' => $vehicle->seller_id,
                'amount' => $amount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'metadata' => [
                    'created_from' => 'web',
                    'ip_address' => request()->ip() ?? null,
                    'user_agent' => request()->userAgent() ?? null,
                ]
            ];

            $order = $this->orderRepo->create($orderData);

            // Reserve vehicle
            $this->vehicleRepo->update($vehicle->id, [
                'status' => 'reserved'
            ]);

            //Clear caches
            Cache::forget("vehicle.{$vehicle->id}");
            Cache::forget('vehicles.listed');
            Cache::forget("seller.{$vehicle->seller_id}.vehicles");

            Log::info('Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'vehicle_id' => $vehicle->id,
                'buyer_id' => $data['buyer_id'],
                'total' => $total
            ]);
            event(new OrderEvent($order, 'created'));
            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw $e;
        }
    }

    /**
     * Process payment for order
     */
    public function processPayment($orderId, array $paymentData)
    {
        DB::beginTransaction();

        try {
            // Get order
            $order = $this->orderRepo->find($orderId);

            // Validate order status
            if ($order->status !== 'pending') {
                throw new \Exception('Order is not in pending status');
            }

            // Process payment
            $transactionId = 'TXN-' . strtoupper(uniqid());

            // Simulate payment processing delay
            sleep(1);

            // Simulate 95% success rate
            if (rand(1, 100) <= 5) {
                throw new \Exception('Payment processing failed');
            }

            //Update order
            $this->orderRepo->update($orderId, [
                'status' => 'payment_completed',
                'payment_method' => $paymentData['method'],
                'payment_transaction_id' => $transactionId,
                'paid_at' => now()
            ]);

            Log::info('Payment processed', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'amount' => $order->total
            ]);

            DB::commit();

            return $this->orderRepo->find($orderId);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Complete order (Mark as completed)
     */
    public function completeOrder($orderId)
    {
        DB::beginTransaction();

        try {
            //Get order
            $order = $this->orderRepo->find($orderId);

            //Validate payment completed
            if (!$order->isPaid()) {
                throw new \Exception('Order payment not completed');
            }

            //Mark order as completed
            $this->orderRepo->update($orderId, [
                'status' => 'completed'
            ]);

            // Mark vehicle as sold
            $this->vehicleService->markAsSold($order->vehicle_id);

            Log::info('Order completed', [
                'order_id' => $orderId,
                'vehicle_id' => $order->vehicle_id,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id
            ]);

            DB::commit();

            return $this->orderRepo->find($orderId);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete order', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder($orderId, $reason = null)
    {
        DB::beginTransaction();

        try {
            //Get order
            $order = $this->orderRepo->find($orderId);

            // Validate can cancel
            if (in_array($order->status, ['completed', 'cancelled'])) {
                throw new \Exception('Cannot cancel this order');
            }

            //Process refund if paid
            if ($order->isPaid()) {
                Log::info('Processing refund', [
                    'order_id' => $orderId,
                    'amount' => $order->total,
                    'transaction_id' => $order->payment_transaction_id
                ]);
            }

            //Update order
            $this->orderRepo->update($orderId, [
                'status' => 'cancelled',
                'notes' => $reason
            ]);

            //Make vehicle available again
            $this->vehicleRepo->update($order->vehicle_id, [
                'status' => 'listed'
            ]);

            //Clear caches
            Cache::forget("vehicle.{$order->vehicle_id}");
            Cache::forget('vehicles.listed');

            Log::info('Order cancelled', [
                'order_id' => $orderId,
                'reason' => $reason
            ]);

            DB::commit();

            return $this->orderRepo->find($orderId);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to cancel order', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get buyer's orders
     */
    public function getBuyerOrders($buyerId)
    {
        return $this->orderRepo->findByBuyer($buyerId);
    }

    /**
     * Get seller's orders
     */
    public function getSellerOrders($sellerId)
    {
        return $this->orderRepo->findBySeller($sellerId);
    }

    /**
     * Get order details
     */
    public function getOrder($orderId)
    {
        return $this->orderRepo->find($orderId);
    }
}
