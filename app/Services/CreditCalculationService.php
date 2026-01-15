<?php

namespace App\Services;

use Carbon\Carbon;

class CreditCalculationService
{
    public static function validateInput(float $otr, float $dp, int $jangkaWaktu): bool
    {
        return $dp < $otr && $jangkaWaktu > 0;
    }

    public static function bunga(int $jangkaWaktu): float
    {
        if ($jangkaWaktu <= 12) return 12;
        if ($jangkaWaktu <= 24) return 14;
        return 16.5;
    }

    public static function pokok(float $otr, float $dp): float
    {
        return $otr - $dp;
    }

    public static function totalUtang(float $pokok, float $bunga): float
    {
        return $pokok + ($pokok * ($bunga / 100));
    }

    public static function angsuran(float $totalUtang, int $jangkaWaktu): float
    {
        return round($totalUtang / $jangkaWaktu);
    }

    public static function jadwal(int $jangkaWaktu, float $angsuran, Carbon $startDate): array
    {
        return collect(range(1, $jangkaWaktu))->map(fn ($i) => [
            'angsuran_ke' => $i,
            'angsuran_per_bulan' => $angsuran,
            'tanggal_jatuh_tempo' => $startDate->copy()->addMonths($i - 1),
            'status_pembayaran' => 'UNPAID',
        ])->toArray();
    }

    public static function denda(float $angsuranPerBulan, Carbon $tanggalJatuhTempo, Carbon $tanggalAcuan): array
    {
        $hariTelat = $tanggalAcuan->diffInDays($tanggalJatuhTempo, false);
        if ($hariTelat <= 0) {
            return ['hari_telat' => 0, 'denda' => 0];
        }
        $denda = $hariTelat * $angsuranPerBulan * 0.001;
        return ['hari_telat' => $hariTelat, 'denda' => $denda];
    }
}