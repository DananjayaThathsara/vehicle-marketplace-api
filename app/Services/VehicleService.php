<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class VehicleService
{

    public function __construct(
        private VehicleRepositoryInterface $vehicleRepo,
        private CacheService $cache

    ) {}

    /**
     * Get all listed vehicles (with caching)
     */
    public function getListedVehicles()
    {
        return $this->cache->remember('vehicles.listed', 3600, function () {
            return $this->vehicleRepo->findListed();
        });
    }

    /**
     * Get vehicle by ID (with caching)
     */
    public function getVehicle($id)
    {
        return $this->cache->remember("vehicle.{$id}", 3600, function () use ($id) {
            return $this->vehicleRepo->find($id);
        });
    }

    /**
     * Search vehicles with filters
     */
    public function searchVehicles(array $filters)
    {
        // Generate cache key from filters
        $cacheKey = 'vehicles.search.' . md5(json_encode($filters));

        return $this->cache->remember($cacheKey, 1800, function () use ($filters) {
            return $this->vehicleRepo->findWithFilters($filters);
        });
    }

    /**
     * Get featured vehicles (with caching)
     */
    public function getFeaturedVehicles($limit = 10)
    {
        return $this->cache->remember('vehicles.featured', 3600, function () use ($limit) {
            return $this->vehicleRepo->findFeatured($limit);
        });
    }

    /**
     * Create new vehicle
     */
    public function createVehicle(array $data)
    {
        DB::beginTransaction();

        try {
            // Create vehicle
            $vehicle = $this->vehicleRepo->create($data);

            // Process images if provided
            if (isset($data['images'])) {
                $vehicle->images = $this->processImages($data['images']);
                $vehicle->save();
            }

            // Clear relevant caches
            $this->clearVehicleCaches();

            // Log activity
            Log::info('Vehicle created', [
                'vehicle_id' => $vehicle->id,
                'seller_id' => $vehicle->seller_id,
                'make' => $vehicle->make,
                'model' => $vehicle->model
            ]);

            DB::commit();

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create vehicle', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw $e;
        }
    }

    /**
     * Update vehicle
     */
    public function updateVehicle($id, array $data)
    {
        DB::beginTransaction();

        try {
            // Get vehicle
            $vehicle = $this->vehicleRepo->find($id);

            // Update
            $vehicle = $this->vehicleRepo->update($id, $data);

            // Process new images if provided
            if (isset($data['images'])) {
                $vehicle->images = $this->processImages($data['images']);
                $vehicle->save();
            }

            // Clear caches
            $this->cache->forget("vehicle.{$id}");
            $this->clearVehicleCaches();

            Log::info('Vehicle updated', [
                'vehicle_id' => $id,
                'changes' => array_keys($data)
            ]);

            DB::commit();

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update vehicle', [
                'vehicle_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Delete vehicle
     */
    public function deleteVehicle($id)
    {
        DB::beginTransaction();

        try {
            // Get vehicle
            $vehicle = $this->vehicleRepo->find($id);

            // Business Rule: Can't delete if has active orders
            if ($vehicle->orders()->whereIn('status', ['pending', 'payment_processing'])->exists()) {
                throw new \Exception('Cannot delete vehicle with active orders');
            }

            // Delete
            $this->vehicleRepo->delete($id);

            // Clear caches
            $this->cache->forget("vehicle.{$id}");
            $this->clearVehicleCaches();

            Log::info('Vehicle deleted', [
                'vehicle_id' => $id,
                'vin' => $vehicle->vin
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete vehicle', [
                'vehicle_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Mark vehicle as sold
     */
    public function markAsSold($id)
    {
        DB::beginTransaction();

        try {
            $vehicle = $this->vehicleRepo->update($id, [
                'status' => 'sold',
                'sold_at' => now()
            ]);

            // Clear caches
            $this->cache->forget("vehicle.{$id}");
            $this->clearVehicleCaches();

            Log::info('Vehicle marked as sold', ['vehicle_id' => $id]);

            DB::commit();

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get seller's vehicles
     */
    public function getSellerVehicles($sellerId)
    {
        return $this->cache->remember("seller.{$sellerId}.vehicles", 1800, function () use ($sellerId) {
            return $this->vehicleRepo->findBySeller($sellerId);
        });
    }

    /**
     * Process uploaded images (placeholder)
     */
    private function processImages($images)
    {
        $processedImages = [];


        if (is_array($images)) {
            foreach ($images as $image) {
                $processedImages[] = 'https://via.placeholder.com/800x600?text=Vehicle+Image';
            }
        }

        return $processedImages;
    }

    /**
     * Clear vehicle-related caches
     */
    private function clearVehicleCaches()
    {
        $this->cache->forget('vehicles.listed');
        $this->cache->forget('vehicles.featured');

    }
}
