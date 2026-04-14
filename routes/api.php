<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\OrderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/search', function (Request $request) {
    $searchService = app(\App\Services\SearchService::class);

    $results = $searchService->search(
        $request->input('q', ''),
        $request->only(['make', 'price_max'])
    );

    return response()->json([
        'success' => true,
        'query' => $request->input('q'),
        'data' => $results
    ]);
});

Route::get('/search/suggestions', function (Request $request) {
    $searchService = app(\App\Services\SearchService::class);

    $suggestions = $searchService->suggestions($request->input('q', ''));

    return response()->json([
        'success' => true,
        'data' => $suggestions
    ]);
});

// Public vehicle routes
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/featured', [VehicleController::class, 'featured']);
Route::get('/vehicles/search', [VehicleController::class, 'search']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Vehicles (authenticated)
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/payment', [OrderController::class, 'payment']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);


});
