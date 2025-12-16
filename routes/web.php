<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// CONTROLLERS
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosImportController;

/* ============================================================
| AUTH
============================================================ */
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ============================================================
| AUTH CHECK (LOGIN REQUIRED)
============================================================ */
Route::middleware(['authcheck'])->group(function () {

    /* ========================================================
    | DASHBOARD (GENERAL)
    ======================================================== */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* ========================================================
    | INVENTORY (SHARED VIEW)
    ======================================================== */
    Route::get('/inventory', fn () => view('inventory.index'))
        ->name('inventory.index');

    /* ========================================================
    | OWNER ONLY
    ======================================================== */
    Route::middleware(['role:owner'])->group(function () {

        /* ================= DASHBOARD OWNER ================= */

        // Statistik Dashboard (AJAX / Partial)
        Route::get('/dashboard/stats',
            [OwnerDashboardController::class, 'stats']
        )->name('dashboard.stats');

        // Kalender Absensi (AJAX JSON)
        Route::get('/api/absensi/rekap/calendar',
            [DashboardController::class, 'getCalendarJson']
        );

        // Detail Rekap Absensi (AJAX JSON)
        Route::get('/api/absensi/rekap/detail',
            [DashboardController::class, 'getRekapDetailJson']
        );

        /* ================= CRUD PEGAWAI ================= */

        Route::resource('pegawai', PegawaiController::class)
            ->except(['show']);

        /* ================= SHIFT MANAGEMENT ================= */

        Route::get('/shifts', [ShiftController::class, 'index'])
            ->name('shifts.index');

        Route::post('/shifts/toggle', [ShiftController::class, 'toggle'])
            ->name('shifts.toggle');

        Route::get('/shifts/week', [ShiftController::class, 'weekJson'])
            ->name('shifts.weekJson');

        Route::post('/shifts/save-batch', [ShiftController::class, 'saveBatch'])
            ->name('shifts.saveBatch');

        /* ================= ABSENSI REKAP ================= */

        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])
            ->name('absensi.rekap');

        /* ================= POS CSV ================= */

        Route::get('/import/csv', [PosImportController::class, 'showUpload'])
            ->name('pos.import.form');

        Route::post('/api/pos/import', [PosImportController::class, 'upload']);
        Route::post('/pos/import', [PosImportController::class, 'import'])
            ->name('api.pos.import');

        /* ================= POS VERIFICATION ================= */

        Route::get('/absensi/rekap/verify/{date}',
            [PosImportController::class, 'showVerificationPage']
        );

        Route::get('/api/pos/verify',
            [PosImportController::class, 'getVerificationByDate']
        );

        Route::post('/api/pos/approve',
            [PosImportController::class, 'approveSingle']
        );

        Route::post('/api/pos/approve-all',
            [PosImportController::class, 'approveAllForDate']
        );
    });

    /* ========================================================
    | PEGAWAI ONLY
    ======================================================== */
    Route::middleware(['role:pegawai'])->group(function () {

        /* ================= ABSENSI UTAMA ================= */

        // Halaman Absen
        Route::get('/absensi', [AbsensiController::class, 'index'])
            ->name('absensi.index');

        // 🔥 Route Check-In (Masuk)
        //Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])
            //->name('absensi.checkin');

        // Route Check-Out (Pulang)
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])
            ->name('absensi.checkout');

        /* ================= ABSENSI PENGGANTI ================= */

        Route::get('/absensi/pengganti',
            [AbsensiController::class, 'showPenggantiForm']
        )->name('absensi.pengganti.form');

        Route::post('/absensi/pengganti/store',
            [AbsensiController::class, 'storePengganti']
        )->name('absensi.pengganti.store');

        /* ================= HISTORY ================= */

        Route::get('/pegawai/history',
            [DashboardController::class, 'history']
        )->name('pegawai.history');
    });
});