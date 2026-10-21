<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Silver',
                'description' => 'Paket intim dan hangat untuk pernikahan sederhana yang tetap berkesan bersama keluarga dan sahabat terdekat.',
                'price' => 35_000_000,
                'max_guests' => 300,
                'features' => [
                    'Dekorasi pelaminan standar',
                    'Tata rias & busana pengantin (1 set)',
                    'Dokumentasi foto (1 fotografer)',
                    'Wedding organizer hari H (4 crew)',
                    'Sound system standar',
                ],
            ],
            [
                'name' => 'Gold',
                'description' => 'Paket favorit dengan dekorasi lebih megah, dokumentasi lengkap, dan tim yang mendampingi sejak persiapan.',
                'price' => 60_000_000,
                'max_guests' => 500,
                'features' => [
                    'Dekorasi pelaminan & area foto',
                    'Tata rias & busana pengantin (2 set)',
                    'Dokumentasi foto & video highlight',
                    'Wedding organizer (2x technical meeting + 8 crew)',
                    'MC profesional',
                    'Sound system & lighting',
                ],
            ],
            [
                'name' => 'Platinum',
                'description' => 'Pengalaman pernikahan mewah tanpa kompromi: konsep custom, dokumentasi cinematic, dan pendampingan penuh.',
                'price' => 95_000_000,
                'max_guests' => 1000,
                'features' => [
                    'Dekorasi custom konsep eksklusif',
                    'Tata rias & busana pengantin (3 set) + keluarga inti',
                    'Dokumentasi foto, video cinematic & drone',
                    'Wedding organizer full service (12 crew)',
                    'MC profesional & live music akustik',
                    'Sound system, lighting & LED screen',
                    'Kamar pengantin hotel (1 malam)',
                ],
            ],
        ];

        foreach ($packages as $data) {
            $package = Package::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'max_guests' => $data['max_guests'],
                'thumbnail' => 'https://picsum.photos/seed/restikey-'.Str::slug($data['name']).'/800/600',
                'is_active' => true,
            ]);

            $package->features()->createMany(
                array_map(fn ($feature) => ['feature' => $feature], $data['features'])
            );
        }
    }
}
