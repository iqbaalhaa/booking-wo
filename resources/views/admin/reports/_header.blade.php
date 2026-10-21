{{-- Tab laporan + filter periode. Variabel: $active ('bookings'|'payments'), $filters, $packages (khusus booking) --}}
<nav class="nav nav-pills gap-2 mb-3" aria-label="Jenis laporan">
    <a @class(['nav-link', 'active' => $active === 'bookings']) href="{{ route('admin.reports.bookings') }}">Laporan booking</a>
    <a @class(['nav-link', 'active' => $active === 'payments']) href="{{ route('admin.reports.payments') }}">Laporan pembayaran</a>
</nav>

@error('sampai')<x-alert type="error">{{ $message }}</x-alert>@enderror

<x-card class="mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label for="dari" class="form-label small mb-1">Dari</label>
            <input id="dari" type="date" name="dari" value="{{ $filters['from']->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-2">
            <label for="sampai" class="form-label small mb-1">Sampai</label>
            <input id="sampai" type="date" name="sampai" value="{{ $filters['to']->toDateString() }}" class="form-control form-control-sm">
        </div>

        @if ($active === 'bookings')
            <div class="col-md-2">
                <label for="basis" class="form-label small mb-1">Periode berdasarkan</label>
                <select id="basis" name="basis" class="form-select form-select-sm">
                    <option value="event_date" @selected($filters['basis'] === 'event_date')>Tanggal acara</option>
                    <option value="created_at" @selected($filters['basis'] === 'created_at')>Tanggal booking dibuat</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="status" class="form-label small mb-1">Status</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Booking::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="paket" class="form-label small mb-1">Paket</label>
                <select id="paket" name="paket" class="form-select form-select-sm">
                    <option value="">Semua paket</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" @selected($filters['package_id'] === $package->id)>{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="col-md-3">
                <label for="tipe" class="form-label small mb-1">Jenis pembayaran</label>
                <select id="tipe" name="tipe" class="form-select form-select-sm">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\Payment::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
        </div>
    </form>
</x-card>
