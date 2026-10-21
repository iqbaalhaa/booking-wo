<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Query laporan & statistik dashboard. Dipakai bersama oleh halaman web, ekspor Excel, dan PDF
 * agar angka di ketiganya selalu sama.
 */
class ReportService
{
    /**
     * @param  array{from: Carbon, to: Carbon, basis: string, status: ?string, package_id: ?int}  $filters
     *         basis: 'event_date' (tanggal acara) atau 'created_at' (tanggal booking dibuat)
     */
    public function bookings(array $filters): Builder
    {
        $range = $filters['basis'] === 'created_at'
            ? [$filters['from']->copy()->startOfDay(), $filters['to']->copy()->endOfDay()]
            : [$filters['from']->toDateString(), $filters['to']->toDateString()];

        return Booking::with(['user', 'package'])
            ->whereBetween($filters['basis'], $range)
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['package_id'], fn ($query, $id) => $query->where('package_id', $id))
            ->orderBy($filters['basis'])
            ->orderBy('id');
    }

    /**
     * Hanya pembayaran terverifikasi (uang yang benar-benar diterima), berdasarkan tanggal verifikasi.
     *
     * @param  array{from: Carbon, to: Carbon, type: ?string}  $filters
     */
    public function payments(array $filters): Builder
    {
        return Payment::verified()
            ->with('booking.user')
            ->whereBetween('verified_at', [$filters['from']->copy()->startOfDay(), $filters['to']->copy()->endOfDay()])
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->orderBy('verified_at')
            ->orderBy('id');
    }

    /**
     * Jumlah booking (berdasarkan tanggal dibuat) & pendapatan terverifikasi per bulan, N bulan terakhir.
     *
     * @return array{labels: list<string>, bookings: list<int>, revenue: list<int>}
     */
    public function monthlySeries(int $months = 12): array
    {
        $start = today()->startOfMonth()->subMonths($months - 1);

        // Raw SQL hanya untuk agregasi laporan (DATE_FORMAT MySQL)
        $bookings = Booking::toBase()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $revenue = Payment::verified()->toBase()
            ->selectRaw("DATE_FORMAT(verified_at, '%Y-%m') as ym, SUM(amount) as total")
            ->where('verified_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $series = ['labels' => [], 'bookings' => [], 'revenue' => []];

        for ($month = $start->copy(); $month->lte(today()); $month->addMonth()) {
            $key = $month->format('Y-m');
            $series['labels'][] = $month->locale('id')->isoFormat('MMM YYYY');
            $series['bookings'][] = (int) ($bookings[$key] ?? 0);
            $series['revenue'][] = (int) ($revenue[$key] ?? 0);
        }

        return $series;
    }
}
