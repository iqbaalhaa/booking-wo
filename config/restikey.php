<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rekening Tujuan Pembayaran
    |--------------------------------------------------------------------------
    */

    'bank' => [
        'name' => env('RESTIKEY_BANK_NAME', 'BCA'),
        'account_number' => env('RESTIKEY_BANK_ACCOUNT_NUMBER', '1234567890'),
        'account_name' => env('RESTIKEY_BANK_ACCOUNT_NAME', 'PT Restikey Wedding Organizer'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aturan Bisnis Booking & Pembayaran
    |--------------------------------------------------------------------------
    */

    // Persentase minimal DP dari total harga
    'dp_percentage' => (int) env('RESTIKEY_DP_PERCENTAGE', 30),

    // Tanggal acara minimal H+N dari hari booking
    'min_booking_days' => (int) env('RESTIKEY_MIN_BOOKING_DAYS', 30),

    // Pelunasan paling lambat H-N sebelum acara
    'payment_deadline_days' => (int) env('RESTIKEY_PAYMENT_DEADLINE_DAYS', 7),

    // Booking confirmed tanpa DP verified dibatalkan otomatis setelah N hari
    'auto_cancel_days' => (int) env('RESTIKEY_AUTO_CANCEL_DAYS', 3),

    // Prefix kode booking: RSK-YYYYMMDD-XXXX
    'booking_code_prefix' => 'RSK',

];
