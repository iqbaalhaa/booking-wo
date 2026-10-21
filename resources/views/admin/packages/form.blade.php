@extends('layouts.admin')

@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')

@php
    $features = old('features', $package->exists ? $package->features->pluck('feature')->all() : []) ?: [''];
@endphp

@section('content')
    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
        @csrf
        @if ($package->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card title="Informasi paket">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama paket</label>
                        <input id="name" name="name" value="{{ old('name', $package->name) }}" maxlength="100"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($package->exists)
                            <div class="form-text">Alamat halaman tetap /paket/{{ $package->slug }} walau nama diubah.</div>
                        @endif
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="price" class="form-label">Harga</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">Rp</span>
                                <input id="price" name="price" inputmode="numeric" placeholder="35.000.000"
                                       value="{{ old('price', $package->price !== null ? number_format($package->price, 0, ',', '.') : '') }}"
                                       class="form-control @error('price') is-invalid @enderror" required>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="max_guests" class="form-label">Maksimal tamu</label>
                            <input id="max_guests" name="max_guests" type="number" min="1" value="{{ old('max_guests', $package->max_guests) }}"
                                   class="form-control @error('max_guests') is-invalid @enderror" required>
                            @error('max_guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea id="description" name="description" rows="4" maxlength="2000"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $package->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </x-card>

                <x-card title="Fitur yang termasuk" class="mt-4">
                    <div data-dynamic-rows>
                        @error('features')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                        <div data-rows class="d-grid gap-2">
                            @foreach ($features as $i => $feature)
                                <div class="input-group has-validation" data-row>
                                    <input name="features[]" value="{{ $feature }}" maxlength="255" aria-label="Fitur {{ $i + 1 }}"
                                           class="form-control @error("features.$i") is-invalid @enderror" placeholder="Contoh: Dokumentasi foto & video">
                                    <button type="button" class="btn btn-outline-danger" data-remove-row aria-label="Hapus fitur">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                    @error("features.$i")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>

                        <template>
                            <div class="input-group" data-row>
                                <input name="features[]" maxlength="255" aria-label="Fitur baru" class="form-control" placeholder="Contoh: Dokumentasi foto & video">
                                <button type="button" class="btn btn-outline-danger" data-remove-row aria-label="Hapus fitur">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </template>

                        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-add-row>
                            <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah fitur
                        </button>
                    </div>
                </x-card>
            </div>

            <div class="col-lg-4">
                <x-card title="Thumbnail">
                    <img id="thumbnail-preview" src="{{ $package->thumbnail_url }}" alt="Pratinjau thumbnail"
                         class="img-fluid rounded mb-3 w-100 object-fit-cover" style="aspect-ratio: 4 / 3" @if (! $package->thumbnail_url) hidden @endif>

                    <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp" data-preview="#thumbnail-preview"
                           class="form-control @error('thumbnail') is-invalid @enderror" aria-describedby="thumbnail-help">
                    <div id="thumbnail-help" class="form-text">JPG, PNG, atau WebP, maksimal 2 MB.</div>
                    @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    @if ($package->thumbnail)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_thumbnail" value="1" id="remove_thumbnail">
                            <label class="form-check-label" for="remove_thumbnail">Hapus thumbnail saat ini</label>
                        </div>
                    @endif
                </x-card>

                <x-card class="mt-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active"
                               @checked(old('is_active', $package->is_active))>
                        <label class="form-check-label" for="is_active">Aktif (tampil untuk customer)</label>
                    </div>
                </x-card>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary flex-grow-1">Simpan</button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-light">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/dynamic-rows.js') }}"></script>
    <script src="{{ asset('js/image-preview.js') }}"></script>
@endpush
