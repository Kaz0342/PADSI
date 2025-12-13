<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Tambahin ini buat debugging user
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController; 
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosImportController;

/* ==================== AUTH ==================== */
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ==================== AUTH PROTECTED ==================== */
// Pastikan middleware 'authcheck' lo udah bener nge-handle session
Route::middleware(['authcheck'])->group(function () {

    /* DASHBOARD (General) */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /* AJAX STATS (Dipake di Dashboard Owner) */
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    /* OWNER + PEGAWAI ACCESS */
    Route::get('/inventory', fn() => view('inventory.index'))->name('inventory.index');

    /* ==================== OWNER ONLY ==================== */
    Route::middleware(['role:owner'])->group(function () {

        /* CRUD Pegawai */
        Route::resource('pegawai', PegawaiController::class)->except(['show']);

        /* SHIFT MANAGEMENT */
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('shifts/toggle', [ShiftController::class, 'toggle'])->name('shifts.toggle');
        Route::get('shifts/week', [ShiftController::class, 'weekJson'])->name('shifts.weekJson');
        Route::post('shifts/save-batch', [ShiftController::class, 'saveBatch'])->name('shifts.saveBatch');

        /* Pengganti (Owner View) */
        Route::get('pengganti/suggestions', [ShiftController::class, 'suggestPengganti'])->name('pengganti.suggestions');
        Route::post('pengganti/assign', [ShiftController::class, 'assignPengganti'])->name('pengganti.assign');

        /* Rekap Absensi */
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');

        /* API Rekap (Calendar & Detail) */
        Route::get('/api/absensi/rekap/calendar', [DashboardController::class, 'getCalendarJson']); 
        Route::get('/api/absensi/rekap/detail', [AbsensiController::class, 'getRekapDetailJson']);

        /* Import CSV POS */
        Route::get('/import/csv', [PosImportController::class,'showUpload'])->name('pos.import.form');
        Route::post('/api/pos/import', [PosImportController::class,'upload']);
        Route::post('/pos/import', [PosImportController::class, 'import'])->name('api.pos.import');

        /* Verifikasi POS */
        Route::get('/absensi/rekap/verify/{date}', [PosImportController::class, 'showVerificationPage']);
        Route::get('/api/pos/verify', [PosImportController::class,'getVerificationByDate']);
        Route::post('/api/pos/approve', [PosImportController::class,'approveSingle']);
        Route::post('/api/pos/approve-all', [PosImportController::class,'approveAllForDate']);
    });

    /* ==================== PEGAWAI & STAFF LAIN ==================== */
    // LOGIC CHECKOUT LO UDAH ADA DISINI, KING! 👇
    Route::middleware(['role:pegawai'])->group(function () {

        /* Absensi Dasar */
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

        /* Check-in & Check-out */
        Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->name('absensi.checkin');
        
        // INI DIA PELAKUNYA 👇 (Pake checkOut 'O' besar sesuai controller)
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');

        /* Pengganti (Pegawai Side) */
        Route::get('/absensi/pengganti', [AbsensiController::class, 'showPenggantiForm'])->name('absensi.pengganti.form');
        Route::post('/absensi/pengganti/store', [AbsensiController::class, 'storePengganti'])->name('absensi.pengganti.store');

        /* History Pribadi */
        Route::get('/pegawai/history', [DashboardController::class, 'history'])->name('pegawai.history');

        /* Debugging (Optional) */
        Route::get('/debug-user', function() { return Auth::user(); });
    });
});