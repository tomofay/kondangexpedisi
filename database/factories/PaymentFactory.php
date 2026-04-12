<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
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
        $status = fake()->randomElement(['pending', 'settlement', 'expire']);

        return [
            'shipment_id' => Shipment::factory(),
            'customer_id' => Customer::factory(),
            'processed_by' => User::factory()->state(['role' => 'kasir']),
            'method' => fake()->randomElement(['midtrans', 'cash', 'transfer', 'e_wallet', 'cod']),
            'status' => $status,
            'amount' => fake()->numberBetween(20000, 200000),
            'midtrans_order_id' => 'PAY-'.Str::upper(Str::random(10)),
            'midtrans_transaction_id' => Str::uuid()->toString(),
            'snap_token' => Str::random(32),
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.Str::random(32),
            'payment_type' => fake()->randomElement(['bank_transfer', 'gopay', 'qris']),
            'bank_name' => fake()->randomElement(['bca', 'bni', 'bri', 'mandiri']),
            'va_number' => fake()->numerify('##########'),
            'fraud_status' => 'accept',
            'signature_key' => Str::random(64),
            'transaction_time' => now()->subMinutes(fake()->numberBetween(1, 120)),
            'paid_at' => $status === 'settlement' ? now()->subMinutes(fake()->numberBetween(1, 120)) : null,
            'gateway_payload' => ['source' => 'factory'],
            'notes' => fake()->sentence(),
        ];
    }
}
