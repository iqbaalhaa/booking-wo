@extends('layouts.admin')

@section('title', $addon->exists ? 'Edit Add-on' : 'Tambah Add-on')

@section('content')
    <form method="POST" novalidate class="admin-form-narrow"
          action="{{ $addon->exists ? route('admin.addons.update', $addon) : route('admin.addons.store') }}">
        @csrf
        @if ($addon->exists) @method('PUT') @endif

        <x-card>
            <div class="mb-3">
                <label for="name" class="form-label">Nama add-on</label>
                <input id="name" name="name" value="{{ old('name', $addon->name) }}" maxlength="100"
                       class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <div class="input-group has-validation">
                    <span class="input-group-text">Rp</span>
                    <input id="price" name="price" inputmode="numeric" placeholder="3.000.000"
                           value="{{ old('price', $addon->price !== null ? number_format($addon->price, 0, ',', '.') : '') }}"
                           class="form-control @error('price') is-invalid @enderror" required aria-describedby="price-help">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div id="price-help" class="form-text">Harga per unit. Customer bisa memilih jumlah saat booking.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" name="description" rows="3" maxlength="1000"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $addon->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active"
                       @checked(old('is_active', $addon->is_active))>
                <label class="form-check-label" for="is_active">Aktif (bisa dipilih saat booking)</label>
            </div>

            <x-slot:footer>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.addons.index') }}" class="btn btn-light">Batal</a>
                </div>
            </x-slot:footer>
        </x-card>
    </form>
@endsection
