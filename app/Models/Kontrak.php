<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    protected $table = 'kontrak';

    protected $fillable = [
        'kontrak_no',
        'client_name',
        'otr',
        'dp',
        'pokok_utang',
        'jangka_waktu',
        'bunga',
        'total_utang',
        'angsuran_per_bulan',
        'start_date',
    ];

    public function jadwalAngsuran()
    {
        return $this->hasMany(JadwalAngsuran::class);
    }
}
