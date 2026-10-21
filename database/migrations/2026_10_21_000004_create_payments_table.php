<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // restrict: catatan keuangan tidak ikut terhapus bersama booking
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('type', 20); // App\Models\Payment::TYPE_*
            $table->unsignedBigInteger('amount');
            $table->string('proof_image');
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('status', 20)->default('pending'); // App\Models\Payment::STATUS_*
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['status', 'verified_at']); // laporan pembayaran
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
