<?php

namespace App\Http\Controllers;

use App\Models\JadwalAngsuran;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function store(Request $request, $jadwalAngsuranId)
    {
        $data = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $result = DB::transaction(function () use ($data, $jadwalAngsuranId) {

            $jadwal = JadwalAngsuran::lockForUpdate()->findOrFail($jadwalAngsuranId);

            if ($jadwal->status_pembayaran === 'PAID') {
                abort(400, 'Jadwal angsuran sudah lunas.');
            }

            $totalSebelumnya = $jadwal->pembayaran()->sum('jumlah_bayar');
            $sisaSebelumnya = $jadwal->angsuran_per_bulan - $totalSebelumnya;

            if ($data['jumlah_bayar'] > $sisaSebelumnya) {
                abort(400, 'Jumlah bayar melebihi sisa tagihan.');
            }

            Pembayaran::create([
                'jadwal_angsuran_id' => $jadwal->id,
                'tanggal_bayar' => Carbon::parse($data['tanggal_bayar']),
                'jumlah_bayar' => $data['jumlah_bayar'],
            ]);

            $totalSetelahnya = $jadwal->pembayaran()->sum('jumlah_bayar');
            $sisaSetelahnya = max(0, $jadwal->angsuran_per_bulan - $totalSetelahnya);

            if ($totalSetelahnya >= $jadwal->angsuran_per_bulan) {
                $jadwal->update([
                    'status_pembayaran' => 'PAID',
                ]);
            }

            return [
                'angsuran_ke' => $jadwal->angsuran_ke,
                'angsuran_per_bulan' => $jadwal->angsuran_per_bulan,

                'dibayar_sebelumnya' => $totalSebelumnya,
                'dibayar_sekarang' => $data['jumlah_bayar'],
                'total_dibayar' => $totalSetelahnya,

                'sisa_tagihan' => $sisaSetelahnya,
                'status_pembayaran' => $jadwal->fresh()->status_pembayaran,
            ];
        });

        return response()->json([
            'message' => 'Pembayaran berhasil diproses.',
            'data' => $result
        ], 201);
    }
}
