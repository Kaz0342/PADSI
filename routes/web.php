<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController; 
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosImportController;

// ==================== AUTH ====================
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ==================== AUTH PROTECTED ====================
Route::middleware(['authcheck'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // OWNER + PEGAWAI
    Route::get('/inventory', fn() => view('inventory.index'))->name('inventory.index');


    // ==================== OWNER ONLY ====================
    Route::middleware(['role:owner'])->group(function () {

        // CRUD Pegawai
        Route::resource('pegawai', PegawaiController::class)->except(['show']);

        // CRUD Shift
        Route::resource('shifts', ShiftController::class)->except(['show', 'create']);

        // Rekap Absensi Owner
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');

        // API Rekap
        Route::get('/api/absensi/rekap/calendar', [AbsensiController::class, 'getRekapCalendarJson']);
        Route::get('/api/absensi/rekap/detail', [AbsensiController::class, 'getRekapDetailJson']);

        // Import CSV POS
        Route::get('/import/csv', [PosImportController::class,'showUpload'])->name('pos.import.form');
        Route::post('/api/pos/import', [PosImportController::class,'upload']);

        // Verifikasi POS
        Route::get('/absensi/rekap/verify/{date}', [PosImportController::class, 'showVerificationPage']);
        Route::get('/api/pos/verify', [PosImportController::class,'getVerificationByDate']);
        Route::post('/api/pos/approve', [PosImportController::class,'approveSingle']);
        Route::post('/api/pos/approve-all', [PosImportController::class,'approveAllForDate']);
    });


    // ==================== PEGAWAI ONLY ====================
    Route::middleware("role:pegawai,kasir,barista,waiter,chef")->group(function () {

        // Absensi Dasar
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

        // Check-in dan Check-out
        Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->name('absensi.checkin');
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');

        // Pengganti
        Route::get('/absensi/pengganti', [AbsensiController::class, 'showPenggantiForm'])->name('absensi.pengganti.form');
        Route::post('/absensi/pengganti/store', [AbsensiController::class, 'storePengganti'])->name('absensi.pengganti.store');
    });
});
