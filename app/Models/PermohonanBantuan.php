<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanBantuan extends Model
{
    

    protected $fillable = [
        'permohonan_id',
        'jenis_bantuan',
        'kategori_bantuan',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}
