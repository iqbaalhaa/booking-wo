<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Package extends Model
{
    use DeletesReplacedFiles, HasFactory;

    protected array $fileColumns = ['thumbnail'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'max_guests',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'max_guests' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Slug dibuat sekali saat paket dibuat; tidak berubah saat nama diedit agar URL yang sudah dibagikan tetap valid
        static::creating(function (Package $package) {
            $package->slug = $package->slug ?: static::uniqueSlug($package->name);
        });

        static::saved(fn () => static::flushCatalogCache());
        static::deleted(fn () => static::flushCatalogCache());
    }

    public const CATALOG_CACHE_KEY = 'packages:active-catalog';

    /**
     * Paket aktif beserta fiturnya, urut harga (dipakai landing, /paket, form booking).
     * Di-cache sampai ada perubahan paket/fitur.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function activeCatalog()
    {
        return Cache::rememberForever(self::CATALOG_CACHE_KEY, fn () => static::active()->with('features')->orderBy('price')->get());
    }

    public static function flushCatalogCache(): void
    {
        Cache::forget(self::CATALOG_CACHE_KEY);
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'paket';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function features(): HasMany
    {
        return $this->hasMany(PackageFeature::class)->orderBy('id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Booking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** URL thumbnail: file di storage public, atau URL eksternal (data seeder). */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            blank($this->thumbnail) => null,
            Str::startsWith($this->thumbnail, 'http') => $this->thumbnail,
            default => Storage::disk('public')->url($this->thumbnail),
        });
    }
}
