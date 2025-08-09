<?php
namespace Database\Factories;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoice = Invoice::inRandomOrder()->first();
        return [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
            'status' => fake()->randomElement(['confirmed', 'refunded', 'cancelled']),
        ];
    }
}
