<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear existing data
        $this->command->info('Clearing existing data...');

        // Create specific test accounts
        $this->command->info('Creating test accounts...');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@vehicle.com',
            'password' => 'password',
            'type' => 'admin',
            'is_active' => true,
            'is_verified' => true,
            'phone' => '555-0001',
            'location' => 'San Francisco, CA',
        ]);

        $seller = User::create([
            'name' => 'John Seller',
            'email' => 'seller@vehicle.com',
            'password' => 'password',
            'type' => 'seller',
            'is_active' => true,
            'is_verified' => true,
            'phone' => '555-0002',
            'location' => 'Los Angeles, CA',
            'bio' => 'Professional car dealer with 10 years experience.',
        ]);

        $buyer = User::create([
            'name' => 'Jane Buyer',
            'email' => 'buyer@vehicle.com',
            'password' => 'password',
            'type' => 'buyer',
            'is_active' => true,
            'is_verified' => true,
            'phone' => '555-0003',
            'location' => 'New York, NY',
        ]);

        // Create random sellers
        $this->command->info('Creating 20 sellers...');
        $sellers = User::factory()->seller()->count(20)->create();

        // Combine test seller with random sellers
        $allSellers = collect([$seller])->concat($sellers);

        // Create random buyers
        $this->command->info('Creating 30 buyers...');
        $buyers = User::factory()->buyer()->count(30)->create();

        // Combine test buyer with random buyers
        $allBuyers = collect([$buyer])->concat($buyers);

        // Create vehicles for each seller
        $this->command->info('Creating vehicles...');

        $allVehicles = collect();

        foreach ($allSellers as $sellerUser) {
            // Each seller has 3-8 vehicles
            $vehicleCount = rand(3, 8);

            $vehicles = Vehicle::factory()
                ->count($vehicleCount)
                ->create([
                    'seller_id' => $sellerUser->id
                ]);

            $allVehicles = $allVehicles->concat($vehicles);
        }

        $this->command->info('Created ' . $allVehicles->count() . ' vehicles');

        // Make some vehicles featured
        $this->command->info('Setting featured vehicles...');
        $allVehicles->random(min(15, $allVehicles->count()))
            ->each(fn($v) => $v->update(['is_featured' => true]));

        // Create orders for sold vehicles
        $this->command->info('Creating orders...');

        $soldVehicles = $allVehicles->where('status', 'sold');

        foreach ($soldVehicles as $vehicle) {
            Order::factory()->create([
                'vehicle_id' => $vehicle->id,
                'seller_id' => $vehicle->seller_id,
                'buyer_id' => $allBuyers->random()->id,
                'amount' => $vehicle->price,
                'status' => 'completed',
            ]);
        }

        $this->command->info('Created ' . Order::count() . ' orders');

        // Create reviews for some vehicles
        $this->command->info('Creating reviews...');

        $listedVehicles = $allVehicles->where('status', 'listed');
        $reviewCount = min(30, $listedVehicles->count());

        foreach ($listedVehicles->random($reviewCount) as $vehicle) {
            Review::factory()->create([
                'vehicle_id' => $vehicle->id,
                'user_id' => $allBuyers->random()->id,
            ]);
        }

        $this->command->info('Created ' . Review::count() . ' reviews');

       
    }
}
