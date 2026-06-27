<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sumbangan extends Model
{
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_MENUNGGU_BAYARAN = 'menunggu_bayaran';
    public const STATUS_DALAM_SEMAKAN = 'dalam_semakan';
    public const STATUS_MENUNGGU_PENGESAHAN = 'menunggu_pengesahan';

    public const PENDING_CONFIRMATION_STATUSES = [
        self::STATUS_MENUNGGU_BAYARAN,
        self::STATUS_DALAM_SEMAKAN,
        self::STATUS_MENUNGGU_PENGESAHAN,
    ];

    protected $fillable = [
        'user_id',
        'no_sumbangan',
        'jumlah_unit',
        'jumlah_keseluruhan',
        'status',
        'kaedah_sumbangan',
        'catatan',
        'donor_snapshot',
        'toyyibpay_bill_code',
        'payment_reference',
        'payment_status',
        'payment_payload',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'jumlah_unit' => 'integer',
        'jumlah_keseluruhan' => 'decimal:2',
        'donor_snapshot' => 'array',
        'payment_payload' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SumbanganItem::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public function scopePendingConfirmation(Builder $query): Builder
    {
        return $query->whereIn('status', self::PENDING_CONFIRMATION_STATUSES);
    }

    public function scopeForDonationYear(Builder $query, int $year): Builder
    {
        return $query->where(function (Builder $query) use ($year) {
            $query
                ->whereYear('paid_at', $year)
                ->orWhere(function (Builder $query) use ($year) {
                    $query
                        ->whereNull('paid_at')
                        ->whereYear('created_at', $year);
                });
        });
    }
}
