<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        private VehicleService $vehicleService
    ) {}

    /**
     * Get all listed vehicles
     */
    public function index()
    {
        $vehicles = $this->vehicleService->getListedVehicles();

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    /**
     * Get single vehicle
     */
    public function show($id)
    {
        try {
            $vehicle = $this->vehicleService->getVehicle($id);

            return response()->json([
                'success' => true,
                'data' => $vehicle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }
    }

    /**
     * Create vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vin' => 'required|string|size:17|unique:vehicles',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:30',
            'mileage' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'status' => 'nullable|in:draft,listed',
        ]);

        $validated['seller_id'] = $request->user()->id;

        try {
            $vehicle = $this->vehicleService->createVehicle($validated);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle created successfully',
                'data' => $vehicle
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vehicle
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'make' => 'sometimes|string|max:50',
            'model' => 'sometimes|string|max:50',
            'year' => 'sometimes|integer|min:1900',
            'color' => 'sometimes|string|max:30',
            'mileage' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:draft,listed,sold',
        ]);

        try {
            $vehicle = $this->vehicleService->updateVehicle($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully',
                'data' => $vehicle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete vehicle
     */
    public function destroy($id)
    {
        try {
            $this->vehicleService->deleteVehicle($id);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search vehicles
     */
    public function search(Request $request)
    {
        $filters = $request->only([
            'make',
            'model',
            'year_min',
            'year_max',
            'price_min',
            'price_max',
            'color',
            'sort'
        ]);

        $vehicles = $this->vehicleService->searchVehicles($filters);

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    /**
     * Get featured vehicles
     */
    public function featured()
    {
        $vehicles = $this->vehicleService->getFeaturedVehicles(10);

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }
}
