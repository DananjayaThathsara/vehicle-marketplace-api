<?php

namespace App\Observers;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class VehicleObserver
{

    /**
     * Handle the Vehicle "creating" event.
     */
    public function creating(Vehicle $vehicle): void
    {
        // Generate a unique slug based on the vehicle's make, model, year, and a unique identifier
        if (empty($vehicle->slug)) {
            $vehicle->slug = Str::slug($vehicle->full_name);
        }

        //If original_price is not set, default it to the current price
        if (empty($vehicle->original_price)) {
            $vehicle->original_price = $vehicle->price;
        }
    }

    /**
     * Handle the Vehicle "created" event.
     */
    public function created(Vehicle $vehicle): void
    {
        Log::info('Vehicle created: ', ['vehicle_id' => $vehicle->id, 'make' => $vehicle->make]);

        Cache::forget('vehicle.featured');
        Cache::forget('vehicle.listed');
    }

    /**
     * Handle the Vehicle "updating" event.
     */
    public function updating(Vehicle $vehicle): void
    {
        // Check if status changed
        if ($vehicle->isDirty('status')) {
            Log::info('Vehicle status changed', [
                'id' => $vehicle->id,
                'from' => $vehicle->getOriginal('status'),
                'to' => $vehicle->status,
            ]);
        }
    }

    /**
     * Handle the Vehicle "updated" event.
     */
    public function updated(Vehicle $vehicle): void
    {
        // Clear cache when vehicle updated
        Cache::forget("vehicle.{$vehicle->id}");
        Cache::forget('vehicles.featured');
        Cache::forget('vehicles.listed');
    }

    /**
     * Handle the Vehicle "deleted" event.
     */
    public function deleted(Vehicle $vehicle): void
    {
        Log::info('Vehicle deleted', ['id' => $vehicle->id]);

        Cache::forget("vehicle.{$vehicle->id}");
        Cache::forget('vehicles.featured');
        Cache::forget('vehicles.listed');
    }

    /**
     * Handle the Vehicle "restored" event.
     */
    public function restored(Vehicle $vehicle): void
    {
        //
    }

    /**
     * Handle the Vehicle "force deleted" event.
     */
    public function forceDeleted(Vehicle $vehicle): void
    {
        //
    }
}
