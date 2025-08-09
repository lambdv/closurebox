<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\EC2Product;
use App\Models\Invoice;
use App\Models\Payment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $testUser = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some organizations
        $organizations = Organization::factory(5)->create();

        // Make the test user a member of all organizations
        foreach ($organizations as $organization) {
            OrganizationMember::factory()->create([
                'user_id' => $testUser->id,
                'organization_id' => $organization->id,
                'role' => fake()->randomElement(['owner', 'member']),
            ]);
        }

        // Create additional users and organizations
        User::factory(10)->create();
        Organization::factory(50)->create();
        OrganizationMember::factory(100)->create();

        // EC2Product::factory(100)->create();
        // Invoice::factory(100)->create();
        // Payment::factory(100)->create();
    }
}
