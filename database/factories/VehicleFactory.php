<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        // Realistic car data
        $makes = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes-Benz', 'Audi', 'Nissan', 'Chevrolet', 'Hyundai', 'Kia'];
        $models = [
            'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Tacoma'],
            'Honda' => ['Accord', 'Civic', 'CR-V', 'Pilot', 'Odyssey'],
            'Ford' => ['F-150', 'Mustang', 'Explorer', 'Escape', 'Edge'],
            'BMW' => ['3 Series', '5 Series', 'X3', 'X5', '7 Series'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'GLC', 'GLE', 'S-Class'],
            'Audi' => ['A4', 'A6', 'Q5', 'Q7', 'A3'],
            'Nissan' => ['Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Maxima'],
            'Chevrolet' => ['Silverado', 'Equinox', 'Malibu', 'Traverse', 'Tahoe'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade'],
            'Kia' => ['Forte', 'Optima', 'Sorento', 'Sportage', 'Telluride'],
        ];

        $colors = ['Black', 'White', 'Silver', 'Gray', 'Red', 'Blue', 'Green', 'Brown', 'Beige'];

        $make = fake()->randomElement($makes);
        $model = fake()->randomElement($models[$make]);
        $year = fake()->numberBetween(2015, 2024);
        $mileage = fake()->numberBetween(5000, 150000);
        $price = fake()->numberBetween(15000, 80000);

        return [
            'vin' => strtoupper(fake()->bothify('?#?#?#?#?#?#?#?#?')), // 17 chars
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'color' => fake()->randomElement($colors),
            'mileage' => $mileage,
            'price' => $price,
            'original_price' => $price + fake()->numberBetween(0, 5000),
            'status' => fake()->randomElement(['draft', 'listed', 'listed', 'listed', 'sold']), // More 'listed'
            'is_featured' => fake()->boolean(20), // 20% featured
            'description' => fake()->paragraphs(3, true),
            'features' => [
                'sunroof' => fake()->boolean(),
                'leather_seats' => fake()->boolean(),
                'navigation' => fake()->boolean(),
                'backup_camera' => fake()->boolean(),
                'bluetooth' => fake()->boolean(),
                'heated_seats' => fake()->boolean(),
                'all_wheel_drive' => fake()->boolean(),
            ],
            'images' => [
                'https://via.placeholder.com/800x600?text=Car+Front',
                'https://via.placeholder.com/800x600?text=Car+Side',
                'https://via.placeholder.com/800x600?text=Car+Interior',
            ],
            'seller_id' => User::factory()->seller(),
            'listed_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the vehicle is listed.
     */
    public function listed()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'listed',
            'listed_at' => now(),
        ]);
    }

    /**
     * Indicate that the vehicle is sold.
     */
    public function sold()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'sold',
            'sold_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the vehicle is featured.
     */
    public function featured()
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
            'status' => 'listed',
        ]);
    }
}
