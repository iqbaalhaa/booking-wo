<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_CONFIRMED => 'Dikonfirmasi',
        self::STATUS_PAID => 'Lunas',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /** Status yang mengunci tanggal acara (hanya boleh satu per tanggal). */
    public const LOCKING_STATUSES = [
        self::STATUS_CONFIRMED,
        self::STATUS_PAID,
        self::STATUS_COMPLETED,
    ];

    /** Status yang masih boleh dibatalkan. */
    public const CANCELLABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
    ];

    protected $fillable = [
        'booking_code',
        'user_id',
        'package_id',
        'event_date',
        'event_location',
        'groom_name',
        'bride_name',
        'guest_count',
        'notes',
        'package_price',
        'addons_total',
        'total_price',
        'status',
        'cancel_reason',
        'confirmed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'guest_count' => 'integer',
            'package_price' => 'integer',
            'addons_total' => 'integer',
            'total_price' => 'integer',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Kode unik format RSK-YYYYMMDD-XXXX (tanggal booking + 4 karakter acak).
     */
    public static function generateCode(?DateTimeInterface $date = null): string
    {
        $prefix = config('restikey.booking_code_prefix').'-'.Carbon::parse($date ?? now())->format('Ymd').'-';

        do {
            $code = $prefix.Str::upper(Str::random(4));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES, true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** Baris add-on beserta snapshot harga. */
    public function bookingAddons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'booking_addons')
            ->withPivot(['addon_name', 'price', 'quantity'])
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /** Booking yang mengunci tanggal (confirmed, paid, completed). */
    public function scopeLocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::LOCKING_STATUSES);
    }

    public function scopeOnDate(Builder $query, DateTimeInterface|string $date): Builder
    {
        return $query->whereDate('event_date', Carbon::parse($date)->toDateString());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('event_date', '>=', today())->orderBy('event_date');
    }
}
