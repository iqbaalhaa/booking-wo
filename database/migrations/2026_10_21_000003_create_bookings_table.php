<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique(); // RSK-YYYYMMDD-XXXX
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // restrict: paket yang sudah dipakai booking tidak boleh dihapus
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->date('event_date');
            $table->string('event_location');
            $table->string('groom_name');
            $table->string('bride_name');
            $table->unsignedInteger('guest_count');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('package_price'); // snapshot
            $table->unsignedBigInteger('addons_total')->default(0);
            $table->unsignedBigInteger('total_price');
            $table->string('status', 20)->default('pending'); // App\Models\Booking::STATUS_*
            $table->text('cancel_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // cek ketersediaan: WHERE event_date = ? AND status IN (...)
            $table->index(['event_date', 'status']);
            $table->index('status');
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            // nullable: riwayat booking tetap utuh lewat snapshot walau add-on dihapus
            $table->foreignId('addon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('addon_name'); // snapshot
            $table->unsignedBigInteger('price'); // snapshot harga satuan
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['booking_id', 'addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
    }
};
