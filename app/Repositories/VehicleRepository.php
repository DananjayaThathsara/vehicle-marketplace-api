<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;

class VehicleRepository implements VehicleRepositoryInterface
{
    /**
     * Get all vehicles with seller relationship
     */
    public function all()
    {
        return Vehicle::with('seller')->latest()->get();
    }

    /**
     * Find vehicle by ID with relationships
     */
    public function find($id)
    {
        return Vehicle::with(['seller', 'reviews', 'orders'])
            ->findOrFail($id);
    }

    /**
     * Create new vehicle
     */
    public function create(array $data)
    {
        return Vehicle::create($data);
    }

    /**
     * Update vehicle
     */
    public function update($id, array $data)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($data);
        return $vehicle->fresh();
    }

    /**
     * Delete vehicle
     */
    public function delete($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return $vehicle->delete();
    }

    /**
     * Find all listed vehicles
     */
    public function findListed()
    {
        return Vehicle::with('seller')
            ->listed()
            ->latest()
            ->paginate(20);
    }

    /**
     * Find vehicles by make
     */
    public function findByMake($make)
    {
        return Vehicle::with('seller')
            ->where('make', $make)
            ->listed()
            ->latest()
            ->paginate(20);
    }

    /**
     * Find with filters (complex query)
     */
    public function findWithFilters(array $filters)
    {
        $query = Vehicle::with('seller')->listed();

        // Filter by make
        if (isset($filters['make'])) {
            $query->where('make', $filters['make']);
        }

        // Filter by model
        if (isset($filters['model'])) {
            $query->where('model', 'like', '%' . $filters['model'] . '%');
        }

        // Filter by year range
        if (isset($filters['year_min'])) {
            $query->where('year', '>=', $filters['year_min']);
        }
        if (isset($filters['year_max'])) {
            $query->where('year', '<=', $filters['year_max']);
        }

        // Filter by price range
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }
        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Filter by color
        if (isset($filters['color'])) {
            $query->where('color', $filters['color']);
        }

        // Sorting
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'year_new':
                    $query->orderBy('year', 'desc');
                    break;
                case 'year_old':
                    $query->orderBy('year', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Find featured vehicles
     */
    public function findFeatured($limit = 10)
    {
        return Vehicle::with('seller')
            ->featured()
            ->listed()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get seller's vehicles
     */
    public function findBySeller($sellerId)
    {
        return Vehicle::with('seller')
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate(20);
    }

    /**
     * Search vehicles by query string
     */
    public function search($query, array $filters = [])
    {
        $search = Vehicle::with('seller')
            ->listed()
            ->where(function ($q) use ($query) {
                $q->where('make', 'like', '%' . $query . '%')
                    ->orWhere('model', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });

        // Apply additional filters
        if (!empty($filters)) {
            $search = $this->applyFiltersToQuery($search, $filters);
        }

        return $search->paginate(20);
    }

    /**
     * Helper: Apply filters to query
     */
    private function applyFiltersToQuery($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }
        return $query;
    }
}
