@extends('layouts.admin')

@section('title', $portfolio->exists ? 'Edit Portofolio' : 'Tambah Portofolio')

@section('content')
    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $portfolio->exists ? route('admin.portfolios.update', $portfolio) : route('admin.portfolios.store') }}">
        @csrf
        @if ($portfolio->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <x-card>
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul</label>
                        <input id="title" name="title" value="{{ old('title', $portfolio->title) }}" maxlength="150" placeholder="Pernikahan Andi & Sari"
                               class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="event_date" class="form-label">Tanggal acara</label>
                            <input id="event_date" name="event_date" type="date" value="{{ old('event_date', $portfolio->event_date?->toDateString()) }}"
                                   class="form-control @error('event_date') is-invalid @enderror">
                            @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="package_id" class="form-label">Paket (opsional)</label>
                            <select id="package_id" name="package_id" class="form-select @error('package_id') is-invalid @enderror">
                                <option value="">Tanpa paket</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" @selected(old('package_id', $portfolio->package_id) == $package->id)>{{ $package->name }}</option>
                                @endforeach
                            </select>
                            @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $portfolio->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-card>
            </div>

            <div class="col-lg-5">
                <x-card title="Foto">
                    <img id="image-preview" src="{{ $portfolio->exists ? $portfolio->image_url : '' }}" alt="Pratinjau foto"
                         class="img-fluid rounded mb-3 w-100 object-fit-cover" style="aspect-ratio: 3 / 2" @if (! $portfolio->exists) hidden @endif>

                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-preview="#image-preview"
                           class="form-control @error('image') is-invalid @enderror" aria-describedby="image-help" @required(! $portfolio->exists)>
                    <div id="image-help" class="form-text">
                        JPG, PNG, atau WebP, maksimal 2 MB.{{ $portfolio->exists ? ' Kosongkan jika tidak ingin mengganti foto.' : '' }}
                    </div>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-card>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary flex-grow-1">Simpan</button>
                    <a href="{{ route('admin.portfolios.index') }}" class="btn btn-light">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/image-preview.js') }}"></script>
@endpush
