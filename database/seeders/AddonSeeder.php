<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            ['name' => 'Dekorasi Ekstra', 'description' => 'Upgrade dekorasi pelaminan dan gerbang dengan bunga segar premium.', 'price' => 7_500_000],
            ['name' => 'MC Profesional', 'description' => 'MC berpengalaman untuk resepsi, bisa dwibahasa.', 'price' => 3_000_000],
            ['name' => 'Dokumentasi Cinematic', 'description' => 'Video cinematic 3–5 menit dan same-day edit.', 'price' => 8_000_000],
            ['name' => 'Katering Tambahan (100 pax)', 'description' => 'Tambahan prasmanan untuk setiap 100 tamu.', 'price' => 6_000_000],
            ['name' => 'Live Music Akustik', 'description' => 'Trio akustik selama 3 jam resepsi.', 'price' => 5_000_000],
            ['name' => 'Photobooth', 'description' => 'Photobooth unlimited cetak selama acara.', 'price' => 3_500_000],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon + ['is_active' => true]);
        }
    }
}
