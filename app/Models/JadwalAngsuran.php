<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalAngsuran extends Model
{
    protected $table = 'jadwal_angsuran';

    protected $fillable = [
        'angsuran_ke',
        'angsuran_per_bulan',
        'tanggal_jatuh_tempo',
        'status_pembayaran',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
