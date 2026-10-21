<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedFiles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use DeletesReplacedFiles, HasFactory;

    protected array $fileColumns = ['image'];

    protected $fillable = [
        'title',
        'description',
        'image',
        'event_date',
        'package_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** URL gambar: file di storage public, atau URL eksternal (data seeder). */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => Str::startsWith($this->image, 'http')
            ? $this->image
            : Storage::disk('public')->url($this->image));
    }
}
