<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\Package;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function bookings(ReportFilterRequest $request)
    {
        $filters = $request->filters();
        $query = $this->reports->bookings($filters);

        return view('admin.reports.bookings', [
            'filters' => $filters,
            'rows' => (clone $query)->paginate(25)->withQueryString(),
            'total' => (clone $query)->sum('total_price'),
            'count' => (clone $query)->count(),
            'packages' => Package::orderBy('price')->get(['id', 'name']),
        ]);
    }

    public function payments(ReportFilterRequest $request)
    {
        $filters = $request->filters();
        $query = $this->reports->payments($filters);

        return view('admin.reports.payments', [
            'filters' => $filters,
            'rows' => (clone $query)->paginate(25)->withQueryString(),
            'total' => (clone $query)->sum('amount'),
            'count' => (clone $query)->count(),
        ]);
    }

    public function exportBookings(ReportFilterRequest $request, string $format)
    {
        $filters = $request->filters();
        $rows = $this->reports->bookings($filters)->get();

        return $this->export($format, 'bookings', 'Laporan Booking', 'laporan-booking', [
            'filters' => $filters,
            'rows' => $rows,
            'total' => $rows->sum('total_price'),
            'packageName' => $filters['package_id'] ? Package::find($filters['package_id'])?->name : null,
        ]);
    }

    public function exportPayments(ReportFilterRequest $request, string $format)
    {
        $filters = $request->filters();
        $rows = $this->reports->payments($filters)->get();

        return $this->export($format, 'payments', 'Laporan Pembayaran', 'laporan-pembayaran', [
            'filters' => $filters,
            'rows' => $rows,
            'total' => $rows->sum('amount'),
        ]);
    }

    private function export(string $format, string $report, string $title, string $slug, array $data)
    {
        $data += ['title' => $title, 'report' => $report];
        $filename = $slug.'-'.$data['filters']['from']->format('Ymd').'-'.$data['filters']['to']->format('Ymd');

        return match ($format) {
            'excel' => Excel::download(new ReportExport('admin.reports.export-excel', $data, $title), "{$filename}.xlsx"),
            // subsetting: hanya glyph yang dipakai yang di-embed (tanpa ini PDF ±900 KB karena font DejaVu utuh)
            'pdf' => Pdf::loadView('admin.reports.export-pdf', $data)
                ->setOption('isFontSubsettingEnabled', true)
                ->setPaper('a4', 'landscape')
                ->download("{$filename}.pdf"),
        };
    }
}
