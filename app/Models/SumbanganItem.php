<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumbanganItem extends Model
{
    protected $fillable = [
        'sumbangan_id',
        'item_id',
        'nama_item',
        'kategori_bantuan',
        'harga_unit',
        'kuantiti',
        'jumlah',
    ];

    protected $casts = [
        'harga_unit' => 'decimal:2',
        'kuantiti' => 'integer',
        'jumlah' => 'decimal:2',
    ];

    public function sumbangan(): BelongsTo
    {
        return $this->belongsTo(Sumbangan::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
