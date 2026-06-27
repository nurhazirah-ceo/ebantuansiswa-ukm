<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanKeluarga extends Model
{
    protected $table = 'permohonan_keluarga';

    protected $fillable = [
        'permohonan_id',
        'jenis',
        'nama',
        'no_kp',
        'hubungan',
        'telefon',
        'pekerjaan',
        'umur',
        'status',
        'kesihatan',
        'pendapatan',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}