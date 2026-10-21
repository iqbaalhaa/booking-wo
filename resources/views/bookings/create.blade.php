@extends('layouts.app')

@section('title', 'Booking Baru')

@php
    $packageId = old('package_id', $selectedPackage?->id);
    $oldAddons = old('addons', []); // sudah dinormalisasi jadi [addon_id => quantity]
@endphp

@section('content')
    <div class="container">
        <h1 class="mb-1">Booking pernikahan</h1>
        <p class="text-body-secondary mb-4">Isi data acara Anda. DP dibayar setelah booking dikonfirmasi oleh tim kami.</p>

        @error('addons')
            <x-alert type="error">{{ $message }}</x-alert>
        @enderror

        <form method="POST" action="{{ route('bookings.store') }}" id="booking-form" novalidate
              data-availability-url="{{ route('availability.json') }}"
              data-messages='@json($messages)'
              data-dp-percentage="{{ config('restikey.dp_percentage') }}">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <x-card title="Paket & tanggal">
                        <div class="mb-3">
                            <label for="package_id" class="form-label">Paket</label>
                            <select id="package_id" name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                                <option value="" data-price="0" data-max-guests="" data-name="">Pilih paket</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" @selected($packageId == $package->id)
                                            data-price="{{ $package->price }}" data-max-guests="{{ $package->max_guests }}" data-name="{{ $package->name }}">
                                        {{ $package->name }} ({{ rupiah($package->price) }}, hingga {{ number_format($package->max_guests, 0, ',', '.') }} tamu)
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label for="event_date" class="form-label">Tanggal acara</label>
                            <input id="event_date" name="event_date" type="date" min="{{ $earliest->toDateString() }}"
                                   value="{{ old('event_date', $selectedDate) }}" aria-describedby="date-status date-help"
                                   class="form-control @error('event_date') is-invalid @enderror" required>
                            @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="date-status" class="date-status" aria-live="polite"></div>
                            <div id="date-help" class="form-text">
                                Paling cepat {{ tanggal_indo($earliest, true) }}.
                                <a href="{{ route('availability.index') }}" target="_blank">Lihat kalender ketersediaan</a>
                            </div>
                        </div>
                    </x-card>

                    <x-card title="Detail acara" class="mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="groom_name" class="form-label">Nama mempelai pria</label>
                                <input id="groom_name" name="groom_name" value="{{ old('groom_name') }}" maxlength="100"
                                       class="form-control @error('groom_name') is-invalid @enderror" required>
                                @error('groom_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bride_name" class="form-label">Nama mempelai wanita</label>
                                <input id="bride_name" name="bride_name" value="{{ old('bride_name') }}" maxlength="100"
                                       class="form-control @error('bride_name') is-invalid @enderror" required>
                                @error('bride_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label for="event_location" class="form-label">Lokasi acara</label>
                                <input id="event_location" name="event_location" value="{{ old('event_location') }}" maxlength="255"
                                       placeholder="Nama gedung/hotel dan kota" class="form-control @error('event_location') is-invalid @enderror" required>
                                @error('event_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="guest_count" class="form-label">Perkiraan jumlah tamu</label>
                                <input id="guest_count" name="guest_count" type="number" min="1" value="{{ old('guest_count') }}"
                                       aria-describedby="guest-help" class="form-control @error('guest_count') is-invalid @enderror" required>
                                @error('guest_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="guest-help" class="form-text"></div>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Catatan (opsional)</label>
                                <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Tema, warna, atau permintaan khusus"
                                          class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </x-card>

                    @if ($addons->isNotEmpty())
                        <x-card title="Layanan tambahan (opsional)" class="mt-4" body-class="p-0">
                            <ul class="list-group list-group-flush">
                                @foreach ($addons as $addon)
                                    <li class="list-group-item addon-row" data-addon data-price="{{ $addon->price }}" data-name="{{ $addon->name }}">
                                        <div class="form-check flex-grow-1">
                                            <input class="form-check-input" type="checkbox" name="addons[{{ $addon->id }}][selected]" value="1"
                                                   id="addon-{{ $addon->id }}" @checked(isset($oldAddons[$addon->id]))>
                                            <label class="form-check-label" for="addon-{{ $addon->id }}">
                                                <span class="fw-medium">{{ $addon->name }}</span>
                                                <span class="text-body-secondary">{{ rupiah($addon->price) }}</span>
                                                @if ($addon->description)
                                                    <span class="d-block small text-body-secondary">{{ $addon->description }}</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="addon-qty">
                                            <label for="addon-qty-{{ $addon->id }}" class="visually-hidden">Jumlah {{ $addon->name }}</label>
                                            <input id="addon-qty-{{ $addon->id }}" type="number" name="addons[{{ $addon->id }}][quantity]"
                                                   min="1" max="{{ config('restikey.max_addon_quantity') }}" value="{{ $oldAddons[$addon->id] ?? 1 }}"
                                                   class="form-control form-control-sm @error('addons.'.$addon->id) is-invalid @enderror">
                                            @error('addons.'.$addon->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card booking-summary">
                        <div class="card-header"><h2 class="h5 mb-0">Ringkasan harga</h2></div>
                        <div class="card-body">
                            <dl class="summary-lines mb-0">
                                <dt id="summary-package-name">Paket</dt>
                                <dd id="summary-package-price">-</dd>
                            </dl>
                            <dl class="summary-lines mb-0" id="summary-addons"></dl>
                            <hr>
                            <dl class="summary-lines summary-total mb-0">
                                <dt>Total</dt>
                                <dd id="summary-total">{{ rupiah(0) }}</dd>
                            </dl>
                            <dl class="summary-lines mb-0 text-body-secondary small">
                                <dt>DP minimal ({{ config('restikey.dp_percentage') }}%)</dt>
                                <dd id="summary-dp">{{ rupiah(0) }}</dd>
                            </dl>
                            <p class="small text-body-secondary mt-3 mb-0">
                                Pelunasan paling lambat H-{{ config('restikey.payment_deadline_days') }} sebelum acara.
                                Total final dihitung ulang oleh sistem saat booking dikirim.
                            </p>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">Kirim booking</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/booking-form.js') }}"></script>
@endpush
