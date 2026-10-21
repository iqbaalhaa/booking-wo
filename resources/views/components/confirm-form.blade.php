{{--
    Tombol aksi berbahaya yang membuka modal konfirmasi sebelum form dikirim.

    <x-confirm-form :action="route('admin.packages.destroy', $package)" method="DELETE"
                    title="Hapus paket?" message="Paket akan dihapus permanen.">
        Hapus
    </x-confirm-form>

    Dengan alasan wajib (mis. pembatalan): tambahkan reason="cancel_reason" reason-label="Alasan pembatalan".
    Modal dirender di @stack('modals') layout agar tidak terpotong tabel/sidebar.
--}}
@props([
    'action',
    'method' => 'POST',
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin?',
    'confirmLabel' => 'Ya, lanjutkan',
    'confirmClass' => 'btn-danger',
    'buttonClass' => 'btn btn-outline-danger btn-sm',
    'reason' => null,
    'reasonLabel' => 'Alasan',
])

@php($id = 'confirm-'.\Illuminate\Support\Str::random(8))

<button type="button" {{ $attributes->merge(['class' => $buttonClass]) }} data-bs-toggle="modal" data-bs-target="#{{ $id }}">
    {{ $slot }}
</button>

@push('modals')
    <div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ $action }}" class="modal-content">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h2 class="modal-title h4" id="{{ $id }}-title">{{ $title }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0">{{ $message }}</p>

                    @if ($reason)
                        <div class="mt-3">
                            <label for="{{ $id }}-reason" class="form-label">{{ $reasonLabel }}</label>
                            <textarea id="{{ $id }}-reason" name="{{ $reason }}" class="form-control" rows="3" maxlength="500" required></textarea>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn {{ $confirmClass }}">{{ $confirmLabel }}</button>
                </div>
            </form>
        </div>
    </div>
@endpush
