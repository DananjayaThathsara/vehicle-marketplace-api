<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-n1', function () {
    //N+1 Problem
    $vehicles = \App\Models\Vehicle::limit(10)->get();

    foreach ($vehicles as $vehicle) {
        echo $vehicle->seller->name . "<br>";  // N queries!
    }

    // Check debugbar - you'll see 11 queries!
});

Route::get('/test-fixed', function () {
    //Fixed with eager loading
    $vehicles = \App\Models\Vehicle::with('seller')->limit(10)->get();

    foreach ($vehicles as $vehicle) {
        echo $vehicle->seller->name . "<br>";  // No extra queries!
    }

    // Check debugbar - only 2 queries!
});

Route::get('redis-cli ping', function () {
    // Analyze a complex query
    $query = \App\Models\Vehicle::query()
        ->where('status', 'listed')
        ->where('make', 'Toyota')
        ->whereBetween('price', [20000, 40000])
        ->orderBy('created_at', 'desc');

    // Get the SQL
    $sql = $query->toSql();
    dump("SQL: " . $sql);

    // Get bindings
    dump("Bindings: ", $query->getBindings());

    // Run EXPLAIN
    $explain = DB::select("EXPLAIN " . $sql, $query->getBindings());
    dump("EXPLAIN:", $explain);

    return "Check output above";
});


Route::get('/test-cache', function () {
    $vehicleService = app(\App\Services\VehicleService::class);

    // First call - hits database
    $start = microtime(true);
    $vehicles = $vehicleService->getListedVehicles();
    $time1 = round((microtime(true) - $start) * 1000, 2);

    // Second call - hits cache
    $start = microtime(true);
    $vehicles2 = $vehicleService->getListedVehicles();
    $time2 = round((microtime(true) - $start) * 1000, 2);

    return "
        <h1>Cache Performance Test</h1>
        <p>First call (database): {$time1}ms</p>
        <p>Second call (cache): {$time2}ms</p>
        <p>Speed improvement: " . round($time1 / $time2, 2) . "x faster!</p>
    ";
});
