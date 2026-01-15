<?php

namespace Database\Seeders;

use App\Models\Kontrak;
use App\Services\CreditCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KontrakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kontrak AGR00001
        $otr = 240_000_000;
        $dp = 48_000_000;
        $jangkaWaktu = 18;
        $startDate = Carbon::create(2024, 1, 25);

        // Hitung finance
        $pokok = CreditCalculationService::pokok($otr, $dp);
        $bunga = CreditCalculationService::bunga($jangkaWaktu);
        $totalUtang = CreditCalculationService::totalUtang($pokok, $bunga);
        $angsuran = CreditCalculationService::angsuran($totalUtang, $jangkaWaktu);

        $kontrak = Kontrak::create([
            'kontrak_no' => 'AGR00001',
            'client_name' => 'SUGUS',
            'otr' => $otr,
            'dp' => $dp,
            'pokok_utang' => $pokok,
            'jangka_waktu' => $jangkaWaktu,
            'bunga' => $bunga,
            'total_utang' => $totalUtang,
            'angsuran_per_bulan' => $angsuran,
            'start_date' => $startDate,
        ]);

        $jadwal = CreditCalculationService::jadwal($jangkaWaktu, $angsuran, $startDate);

        foreach ($jadwal as $row) {
            if ($row['angsuran_ke'] <= 5) {
                $row['status_pembayaran'] = 'PAID';
            }

            $kontrak->jadwalAngsuran()->create($row);
        }
    }
}
