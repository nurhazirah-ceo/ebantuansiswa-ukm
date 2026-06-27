<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanPelajar extends Model
{
    protected $table = 'permohonan_pelajar';

    protected $fillable = [
        'permohonan_id',
        'nama_penuh',
        'no_matrik',
        'email_ukm',
        'no_telefon',
        'fakulti',
        'tahun_pengajian',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}
