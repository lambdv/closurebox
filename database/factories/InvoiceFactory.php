<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\EC2Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::inRandomOrder()->first()->id,
            'type' => fake()->randomElement(['usage', 'subscription', 'one-time']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'due' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => fake()->randomElement(['paid', 'unpaid', 'overdue']),
            'ec2_product_id' => EC2Product::inRandomOrder()->first()?->id,
        ];
    }
}
