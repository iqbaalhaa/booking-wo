{{--
    <x-card title="Judul">
        <x-slot:actions>tombol di header</x-slot:actions>
        isi
        <x-slot:footer>footer</x-slot:footer>
    </x-card>
    Pakai body-class="p-0" untuk tabel yang menempel ke tepi kartu.
--}}
@props(['title' => null, 'bodyClass' => ''])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            @if ($title)
                <h2 class="h5 mb-0">{{ $title }}</h2>
            @endif
            {{ $actions ?? '' }}
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
