<?php

namespace Database\Seeders;

use App\Models\BlockedDate;
use Illuminate\Database\Seeder;

class BlockedDateSeeder extends Seeder
{
    public function run(): void
    {
        // Offset hari dari hari ini; tidak bentrok dengan tanggal di BookingSeeder
        $dates = [
            33 => 'Libur tim',
            34 => 'Libur tim',
            40 => 'Perawatan peralatan',
            110 => 'Acara internal Restikey',
            140 => 'Libur nasional',
        ];

        foreach ($dates as $offset => $reason) {
            BlockedDate::create([
                'date' => today()->addDays($offset),
                'reason' => $reason,
            ]);
        }
    }
}
