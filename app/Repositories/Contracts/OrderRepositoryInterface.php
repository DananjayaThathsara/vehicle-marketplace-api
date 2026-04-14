<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function findByBuyer($buyerId);
    public function findBySeller($sellerId);
    public function findByVehicle($vehicleId);
    public function findPending();
    public function findCompleted();
    public function findRecent($days = 7);
}
