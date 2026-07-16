<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDonation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'amount',
        'message',
        'bill_code',
        'transaction_id',
        'payment_status',
        'paid_at',
        'resolved_at',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_SUCCESS);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    public function scopeForPaymentYear(Builder $query, int $year): Builder
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
