<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'jadwal_angsuran_id',
        'tanggal_bayar',
        'jumlah_bayar',
    ];

    public function jadwalAngsuran()
    {
        return $this->belongsTo(JadwalAngsuran::class);
    }
}
