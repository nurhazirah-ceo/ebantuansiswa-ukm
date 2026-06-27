<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalDonation extends Model
{
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_AWAITING_DELIVERY = 'awaiting_delivery';
    public const STATUS_RECEIVED = 'received';

    public const DELIVERY_SELF = 'serahan_sendiri';
    public const DELIVERY_COURIER = 'pos_kurier';

    protected $fillable = [
        'user_id',
        'category',
        'item_name',
        'quantity',
        'item_condition',
        'description',
        'image_path',
        'donor_phone',
        'donor_address',
        'delivery_method',
        'courier_name',
        'tracking_number',
        'expected_delivery_date',
        'status',
        'admin_note',
        'rejection_reason',
        'approved_at',
        'received_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function getCategoryLabelAttribute(): string
    {
        return Item::DONATION_CATEGORIES[$this->category]['title'] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'Menunggu Semakan',
            self::STATUS_APPROVED => 'Diluluskan',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_AWAITING_DELIVERY => 'Menunggu Serahan',
            self::STATUS_RECEIVED => 'Barang Diterima',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'bg-amber-100 text-amber-700 border border-amber-200',
            self::STATUS_APPROVED => 'bg-blue-100 text-blue-700 border border-blue-200',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-700 border border-rose-200',
            self::STATUS_AWAITING_DELIVERY => 'bg-cyan-100 text-cyan-700 border border-cyan-200',
            self::STATUS_RECEIVED => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return match ($this->delivery_method) {
            self::DELIVERY_SELF => 'Serahan sendiri',
            self::DELIVERY_COURIER => 'Pos/Kurier',
            default => 'Belum dikemaskini',
        };
    }

    public function canUpdateDelivery(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_AWAITING_DELIVERY], true);
    }

    public function canReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function canMarkReceived(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_AWAITING_DELIVERY], true);
    }
}
