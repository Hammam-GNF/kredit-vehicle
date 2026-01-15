<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Services\CreditCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class KontrakController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'kontrak_no' => 'required|unique:kontrak,kontrak_no',
            'client_name' => 'required|string|max:255',
            'otr' => 'required|numeric|min:1',
            'dp' => 'required|numeric|min:0',
            'jangka_waktu' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        $kontrak = DB::transaction(function () use ($data) {
            $pokok = CreditCalculationService::pokok($data['otr'], $data['dp']);
            $bunga = CreditCalculationService::bunga($data['jangka_waktu']);
            $total = CreditCalculationService::totalUtang($pokok, $bunga);
            $angsuran = CreditCalculationService::angsuran($total, $data['jangka_waktu']);

            $kontrak = Kontrak::create([
                ...$data,
                'pokok_utang' => $pokok,
                'bunga' => $bunga,
                'total_utang' => $total,
                'angsuran_per_bulan' => $angsuran,
            ]);

            foreach (
                CreditCalculationService::jadwal(
                    $data['jangka_waktu'],
                    $angsuran,
                    Carbon::parse($data['start_date'])
                ) as $row
            ) {
                $kontrak->jadwalAngsuran()->create($row);
            }

            return $kontrak->load('jadwalAngsuran');
        });

        return response()->json([
            'message' => 'Kontrak berhasil dibuat',
            'data' => [
                'kontrak_id' => $kontrak->id,
                'kontrak_no' => $kontrak->kontrak_no,
                'client_name' => $kontrak->client_name,

                'otr' => $kontrak->otr,
                'dp' => $kontrak->dp,
                'pokok_utang' => $kontrak->pokok_utang,
                'bunga' => $kontrak->bunga,
                'total_utang' => $kontrak->total_utang,
                'angsuran_per_bulan' => $kontrak->angsuran_per_bulan,

                'jangka_waktu' => $kontrak->jangka_waktu,
                'start_date' => $kontrak->start_date,

                'jadwal_summary' => [
                    'total_angsuran' => $kontrak->jadwalAngsuran->count(),
                    'jatuh_tempo_pertama' => optional($kontrak->jadwalAngsuran->first())->tanggal_jatuh_tempo,
                    'jatuh_tempo_terakhir' => optional($kontrak->jadwalAngsuran->last())->tanggal_jatuh_tempo,
                ],
            ],
        ], 201);
    }

    public function reportDenda(Request $request, $kontrakId)
    {
        $tanggalAcuan = $request->input('tanggal_acuan', now());
        $tanggalAcuan = Carbon::parse($tanggalAcuan);

        $kontrak = Kontrak::with(['jadwalAngsuran' => function ($q) use ($tanggalAcuan) {
            $q->where('status_pembayaran', 'UNPAID')
            ->where('tanggal_jatuh_tempo', '<=', $tanggalAcuan);
        }])->findOrFail($kontrakId);

        $results = [];

        foreach ($kontrak->jadwalAngsuran as $jadwal) {
            $dendaData = CreditCalculationService::denda(
                $jadwal->angsuran_per_bulan,
                Carbon::parse($jadwal->tanggal_jatuh_tempo),
                $tanggalAcuan
            );

            $results[] = [
                'kontrak_no' => $kontrak->kontrak_no,
                'client_name' => $kontrak->client_name,
                'angsuran_ke' => $jadwal->angsuran_ke,
                'hari_keterlambatan' => $dendaData['hari_telat'],
                'total_denda' => $dendaData['denda'],
            ];
        }

        return response()->json([
            'message' => 'Laporan denda keterlambatan',
            'tanggal_laporan' => Carbon::now()->toDateString(),
            'tanggal_acuan' => $tanggalAcuan->toDateString(),
            'data' => $results
        ]);
    }
}
