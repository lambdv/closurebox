<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EC2Product>
 */
class EC2ProductFactory extends Factory
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
            'instance_id' => fake()->uuid(),
            'status' => fake()->randomElement(['pending', 'active', 'stopped', 'terminated']),
            'details' => [
                'instance_type' => fake()->randomElement(['t2.micro', 't2.small', 't2.medium', 't3.micro', 't3.small']),
                'region' => fake()->randomElement(['us-east-1', 'us-west-2', 'eu-west-1', 'ap-southeast-1']),
                'launch_time' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'public_ip' => fake()->ipv4(),
                'private_ip' => fake()->localIpv4(),
            ],
        ];
    }
}
