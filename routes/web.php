<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('landing');
Route::get('/paket', [PackageController::class, 'index'])->name('packages.index');
Route::get('/paket/{package:slug}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/portofolio', [PortfolioController::class, 'index'])->name('portfolios.index');
Route::get('/cek-tanggal', [AvailabilityController::class, 'index'])->name('availability.index');
Route::get('/api/availability', [AvailabilityController::class, 'month'])->middleware('throttle:60,1')->name('availability.json');

Auth::routes();

/*
|--------------------------------------------------------------------------
| Customer (login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('/booking', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/booking/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/{booking:booking_code}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/booking/{booking:booking_code}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/booking/{booking:booking_code}/bayar', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/booking/{booking:booking_code}/bayar', [PaymentController::class, 'store'])->name('payments.store');
    // Bukti transfer (disk privat): pemilik booking & admin
    Route::get('/pembayaran/{payment}/bukti', [PaymentController::class, 'proof'])->name('payments.proof');

    Route::post('/booking/{booking:booking_code}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking:booking_code}', [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking:booking_code}/konfirmasi', [Admin\BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking:booking_code}/batal', [Admin\BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking:booking_code}/selesai', [Admin\BookingController::class, 'complete'])->name('bookings.complete');

    Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verifikasi', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/tolak', [Admin\PaymentController::class, 'reject'])->name('payments.reject');

    Route::patch('/packages/{package}/toggle', [Admin\PackageController::class, 'toggle'])->name('packages.toggle');
    Route::resource('packages', Admin\PackageController::class)->except('show');

    Route::patch('/addons/{addon}/toggle', [Admin\AddonController::class, 'toggle'])->name('addons.toggle');
    Route::resource('addons', Admin\AddonController::class)->except('show');

    Route::resource('portfolios', Admin\PortfolioController::class)->except('show');

    Route::resource('blocked-dates', Admin\BlockedDateController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['blocked-dates' => 'blockedDate']);

    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/publish', [Admin\ReviewController::class, 'toggle'])->name('reviews.toggle');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/reports/bookings', [Admin\ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/bookings/export/{format}', [Admin\ReportController::class, 'exportBookings'])
        ->whereIn('format', ['excel', 'pdf'])->name('reports.bookings.export');
    Route::get('/reports/payments', [Admin\ReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/payments/export/{format}', [Admin\ReportController::class, 'exportPayments'])
        ->whereIn('format', ['excel', 'pdf'])->name('reports.payments.export');
});
