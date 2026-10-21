<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'feature',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Package::flushCatalogCache());
        static::deleted(fn () => Package::flushCatalogCache());
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
