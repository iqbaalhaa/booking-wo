<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Default: pembayaran DP minimal (persentase dari config) berstatus pending.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->confirmed(),
            'type' => Payment::TYPE_DP,
            'amount' => fn (array $attributes) => (int) ceil(
                Booking::find($attributes['booking_id'])->total_price * config('restikey.dp_percentage') / 100
            ),
            'proof_image' => 'https://picsum.photos/seed/'.fake()->uuid().'/600/800',
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI']),
            'account_name' => fake()->name(),
            'status' => Payment::STATUS_PENDING,
        ];
    }

    /** Pelunasan sebesar sisa tagihan setelah DP minimal. */
    public function pelunasan(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Payment::TYPE_PELUNASAN,
            'amount' => fn (array $attributes) => Booking::find($attributes['booking_id'])->total_price - (int) ceil(
                Booking::find($attributes['booking_id'])->total_price * config('restikey.dp_percentage') / 100
            ),
        ]);
    }

    public function verified(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $admin?->id ?? User::factory()->admin(),
            'verified_at' => now(),
        ]);
    }

    public function rejected(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_REJECTED,
            'rejection_reason' => 'Bukti transfer tidak terbaca.',
            'verified_by' => $admin?->id ?? User::factory()->admin(),
            'verified_at' => now(),
        ]);
    }
}
