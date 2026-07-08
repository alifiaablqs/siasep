<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LokasiAsetApiController;
use App\Http\Controllers\Api\JenisKategoriApiController;
use App\Http\Controllers\Api\KategoriAsetApiController;
use App\Http\Controllers\Api\DataAsetApiController;
use App\Http\Controllers\Api\StockOpnameApiController;
use App\Http\Controllers\Api\PengajuanPerbaikanApiController;
use App\Http\Controllers\Api\MonitoringApiController;

// Public routes
Route::post('login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Lokasi Aset
    Route::apiResource('lokasi-aset', LokasiAsetApiController::class);

    // Jenis Kategori Aset
    Route::apiResource('jenis-kategori', JenisKategoriApiController::class);

    // Kategori Aset
    Route::apiResource('kategori-aset', KategoriAsetApiController::class);

    // Cetak Label Aset
    Route::post('data-aset/cetak-label', [DataAsetApiController::class, 'cetakLabel']);
    Route::get('data-aset/cetak-label-lokasi/{lokasi_id}', [DataAsetApiController::class, 'cetakLabelLokasi']);

    // Data Aset
    Route::apiResource('data-aset', DataAsetApiController::class);

    // Stock Opname
    Route::get('stock-opname', [StockOpnameApiController::class, 'index']);
    Route::post('stock-opname', [StockOpnameApiController::class, 'store']);
    Route::get('stock-opname/{id}', [StockOpnameApiController::class, 'show']);
    Route::put('stock-opname/{id}/status', [StockOpnameApiController::class, 'updateStatus']);
    Route::post('stock-opname/{id}/sync', [StockOpnameApiController::class, 'sync']);
    Route::post('stock-opname/scan', [StockOpnameApiController::class, 'scan']);

    // Pengajuan Perbaikan
    Route::get('perbaikan', [PengajuanPerbaikanApiController::class, 'index']);
    Route::post('perbaikan', [PengajuanPerbaikanApiController::class, 'store']);
    Route::get('perbaikan/{id}', [PengajuanPerbaikanApiController::class, 'show']);
    Route::put('perbaikan/{id}/proses', [PengajuanPerbaikanApiController::class, 'proses']);
    Route::put('perbaikan/{id}/selesai', [PengajuanPerbaikanApiController::class, 'selesai']);

    // Monitoring (Log Aset)
    Route::get('monitoring', [MonitoringApiController::class, 'index']);
    Route::post('monitoring', [MonitoringApiController::class, 'store']);
});

