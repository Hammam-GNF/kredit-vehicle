<?php

use App\Http\Controllers\KontrakController;
use App\Http\Controllers\PembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/kontrak/{kontrakId}/denda-report', [KontrakController::class, 'reportDenda']);

Route::post('/kontrak', [KontrakController::class, 'store']);

Route::post('/angsuran/{jadwalAngsuranId}', [PembayaranController::class, 'store']);