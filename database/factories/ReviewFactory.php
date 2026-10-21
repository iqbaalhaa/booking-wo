<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->completed(),
            'user_id' => fn (array $attributes) => Booking::find($attributes['booking_id'])->user_id,
            'rating' => fake()->numberBetween(4, 5),
            // Faker id_ID tetap menghasilkan lorem ipsum untuk paragraph(), jadi pakai contoh kalimat sendiri
            'comment' => fake()->randomElement([
                'Tim Restikey sangat sigap dari technical meeting sampai hari H. Kami tinggal menikmati acara tanpa memikirkan apa pun.',
                'Dekorasinya persis seperti konsep yang kami minta, tamu banyak yang memuji. Terima kasih Restikey!',
                'Rundown berjalan tepat waktu dan krunya ramah. Keluarga besar kami merasa sangat terbantu.',
                'Hasil dokumentasinya cantik sekali, momen akad dan resepsi terekam semua dengan baik.',
            ]),
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
