<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    public const TYPE_DP = 'dp';
    public const TYPE_PELUNASAN = 'pelunasan';

    public const TYPES = [
        self::TYPE_DP => 'Uang Muka (DP)',
        self::TYPE_PELUNASAN => 'Pelunasan',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    protected $fillable = [
        'booking_id',
        'type',
        'amount',
        'proof_image',
        'bank_name',
        'account_name',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeDp(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DP);
    }

    public function scopePelunasan(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PELUNASAN);
    }

    protected function proofUrl(): Attribute
    {
        return Attribute::get(fn () => Str::startsWith($this->proof_image, 'http')
            ? $this->proof_image
            : Storage::disk('public')->url($this->proof_image));
    }

    public function isPdf(): bool
    {
        return Str::endsWith(Str::lower($this->proof_image), '.pdf');
    }
}
