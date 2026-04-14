<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create();
        $amount = $vehicle->price;
        $tax = round($amount * 0.08, 2); // 8% tax
        $total = $amount + $tax;

        return [
            'order_number' => 'ORD-' . strtoupper(fake()->bothify('########')),
            'vehicle_id' => $vehicle->id,
            'buyer_id' => User::factory()->buyer(),
            'seller_id' => $vehicle->seller_id,
            'amount' => $amount,
            'tax' => $tax,
            'total' => $total,
            'status' => fake()->randomElement([
                'pending',
                'payment_completed',
                'payment_completed',  // More completed orders
                'completed',
                'completed'
            ]),
            'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'cash']),
            'payment_transaction_id' => 'TXN-' . strtoupper(fake()->bothify('##########')),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            ],
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
