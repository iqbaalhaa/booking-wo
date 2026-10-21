{{-- Kartu paket untuk landing & /paket. Variabel: $package (dengan features), $popular (bool) --}}
<article @class(['package-card', 'is-popular' => $popular ?? false])>
    @if ($popular ?? false)
        <span class="popular-badge">Paling banyak dipilih</span>
    @endif
    @if ($package->thumbnail_url)
        <img src="{{ $package->thumbnail_url }}" alt="Contoh dekorasi paket {{ $package->name }}" loading="lazy">
    @endif
    <div class="package-body">
        <h3 class="h2 mb-1">{{ $package->name }}</h3>
        <div class="package-price">{{ rupiah($package->price) }}</div>
        <div class="small text-body-secondary">Hingga {{ number_format($package->max_guests, 0, ',', '.') }} tamu</div>
        <ul>
            @foreach ($package->features as $feature)
                <li><i class="bi bi-check2" aria-hidden="true"></i> {{ $feature->feature }}</li>
            @endforeach
        </ul>
        <a href="{{ route('packages.show', $package) }}" @class(['btn w-100', 'btn-primary' => $popular ?? false, 'btn-outline-primary' => ! ($popular ?? false)])>
            Lihat detail paket {{ $package->name }}
        </a>
    </div>
</article>
