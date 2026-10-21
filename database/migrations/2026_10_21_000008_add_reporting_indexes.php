<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk query yang muncul setelah dashboard & laporan dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // grafik booking per bulan & laporan berbasis tanggal dibuat
            $table->index('created_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            // daftar pembayaran admin: filter status, urut tanggal unggah
            $table->index(['status', 'created_at']);
        });

        Schema::table('portfolios', function (Blueprint $table) {
            // galeri & landing diurutkan berdasarkan tanggal acara
            $table->index('event_date');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // ulasan published terbaru di landing & detail paket
            $table->index(['is_published', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropIndex(['created_at']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['status', 'created_at']));
        Schema::table('portfolios', fn (Blueprint $table) => $table->dropIndex(['event_date']));
        Schema::table('reviews', fn (Blueprint $table) => $table->dropIndex(['is_published', 'created_at']));
    }
};
