<?php

namespace App\Services;

use App\Models\Vehicle;

class SearchService
{
    /**
     * Search vehicles (uses Algolia if configured, else database)
     */
    public function search(string $query, array $filters = [])
    {
        // If Algolia is configured
        if (config('scout.driver') === 'algolia' && config('scout.algolia.id')) {
            return $this->algoliaSearch($query, $filters);
        }

        // Fallback to database search
        return $this->databaseSearch($query, $filters);
    }

    /**
     * Algolia search
     */
    private function algoliaSearch(string $query, array $filters)
    {
        $search = Vehicle::search($query);

        // Apply filters
        if (isset($filters['make'])) {
            $search->where('make', $filters['make']);
        }

        return $search->paginate(20);
    }

    /**
     * Database fallback search
     */
    private function databaseSearch(string $query, array $filters)
    {
        $search = Vehicle::query()
            ->with('seller')
            ->where('status', 'listed')
            ->where(function ($q) use ($query) {
                $q->where('make', 'like', "%{$query}%")
                    ->orWhere('model', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        // Apply filters
        if (isset($filters['make'])) {
            $search->where('make', $filters['make']);
        }

        if (isset($filters['price_max'])) {
            $search->where('price', '<=', $filters['price_max']);
        }

        return $search->paginate(20);
    }

    /**
     * Autocomplete suggestions
     */
    public function suggestions(string $query)
    {
        return Vehicle::search($query)
            ->take(5)
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'text' => $vehicle->full_name,
                ];
            });
    }
}
