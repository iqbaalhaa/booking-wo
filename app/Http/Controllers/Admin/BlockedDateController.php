<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockedDateRequest;
use App\Models\BlockedDate;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockedDateController extends Controller
{
    public function index(Request $request)
    {
        $showPast = $request->boolean('lewat');

        $blockedDates = BlockedDate::query()
            ->when(! $showPast, fn ($query) => $query->whereDate('date', '>=', today()))
            ->orderBy('date', $showPast ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blocked-dates.index', compact('blockedDates', 'showPast'));
    }

    public function store(BlockedDateRequest $request)
    {
        $dates = $request->dates();

        $created = DB::transaction(fn () => $dates
            ->filter(fn ($date) => BlockedDate::firstOrCreate(
                ['date' => $date->toDateString()],
                ['reason' => $request->validated('reason')],
            )->wasRecentlyCreated)
            ->count());

        $skipped = $dates->count() - $created;
        $message = "{$created} tanggal berhasil diblokir.".($skipped ? " {$skipped} tanggal sudah diblokir sebelumnya." : '');

        // Booking pending tidak menghalangi pemblokiran, tetapi admin perlu tahu
        $pending = Booking::pending()
            ->whereBetween('event_date', [$request->startDate()->toDateString(), $request->endDate()->toDateString()])
            ->count();

        return redirect()->route('admin.blocked-dates.index')
            ->with('success', $message)
            ->with($pending ? ['warning' => "Ada {$pending} booking pending di tanggal yang diblokir. Tinjau booking tersebut."] : []);
    }

    public function destroy(BlockedDate $blockedDate)
    {
        $blockedDate->delete();

        return back()->with('success', 'Tanggal '.tanggal_indo($blockedDate->date).' dibuka kembali.');
    }
}
