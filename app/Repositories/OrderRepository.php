<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function all()
    {
        return Order::with(['vehicle', 'buyer', 'seller'])
            ->latest()
            ->paginate(20);
    }

    public function find($id)
    {
        return Order::with(['vehicle', 'buyer', 'seller'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order->fresh();
    }

    public function findByBuyer($buyerId)
    {
        return Order::with(['vehicle', 'seller'])
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(20);
    }

    public function findBySeller($sellerId)
    {
        return Order::with(['vehicle', 'buyer'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate(20);
    }

    public function findByVehicle($vehicleId)
    {
        return Order::with(['buyer', 'seller'])
            ->where('vehicle_id', $vehicleId)
            ->latest()
            ->get();
    }

    public function findPending()
    {
        return Order::with(['vehicle', 'buyer', 'seller'])
            ->pending()
            ->latest()
            ->get();
    }

    public function findCompleted()
    {
        return Order::with(['vehicle', 'buyer', 'seller'])
            ->completed()
            ->latest()
            ->paginate(20);
    }

    public function findRecent($days = 7)
    {
        return Order::with(['vehicle', 'buyer', 'seller'])
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->get();
    }
}
