@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="container">
        <h1 class="mb-4">Profil saya</h1>

        <div class="row g-4">
            <div class="col-lg-7">
                <x-card title="Data diri">
                    <form method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" value="{{ $user->email }}" class="form-control" disabled aria-describedby="email-help">
                            <div id="email-help" class="form-text">Email dipakai untuk masuk dan tidak bisa diubah di sini.</div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama lengkap</label>
                            <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor telepon / WhatsApp</label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"
                                   placeholder="081234567890" class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea id="address" name="address" rows="3" maxlength="1000" autocomplete="street-address"
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan profil</button>
                    </form>
                </x-card>
            </div>

            <div class="col-lg-5">
                <x-card title="Ganti kata sandi">
                    <form method="POST" action="{{ route('profile.password') }}" novalidate>
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Kata sandi saat ini</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                                   class="form-control @error('current_password', 'password') is-invalid @enderror" required>
                            @error('current_password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Kata sandi baru</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" aria-describedby="password-help"
                                   class="form-control @error('password', 'password') is-invalid @enderror" required>
                            <div id="password-help" class="form-text">Minimal 8 karakter.</div>
                            @error('password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Ulangi kata sandi baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">Ganti kata sandi</button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
@endsection
