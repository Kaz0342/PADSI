<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ================= CONTROLLERS =================
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosImportController;

/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / LOGOUT)
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| AUTH CHECK (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['authcheck'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD UMUM
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | INVENTORY (SHARED)
    |--------------------------------------------------------------------------
    */
    Route::get('/inventory', fn () => view('inventory.index'))
        ->name('inventory.index');

    /*
    |--------------------------------------------------------------------------
    | OWNER ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['owner'])->group(function () {

        // ===== DASHBOARD OWNER =====
        Route::get('/dashboard/stats', [OwnerDashboardController::class, 'stats'])
            ->name('dashboard.stats');

        // ===== KALENDER & REKAP ABSENSI (AJAX) =====
        Route::get('/api/absensi/rekap/calendar',
            [DashboardController::class, 'getCalendarJson']
        );

        Route::get('/api/absensi/rekap/detail',
            [DashboardController::class, 'getRekapDetailJson']
        );

        // ===== CRUD PEGAWAI =====
        Route::resource('pegawai', PegawaiController::class)
            ->except(['show']);

        // ===== SHIFT MANAGEMENT =====
        Route::get('/shifts', [ShiftController::class, 'index'])
            ->name('shifts.index');

        Route::post('/shifts/toggle', [ShiftController::class, 'toggle'])
            ->name('shifts.toggle');

        Route::get('/shifts/week', [ShiftController::class, 'weekJson'])
            ->name('shifts.weekJson');

        Route::post('/shifts/save-batch', [ShiftController::class, 'saveBatch'])
            ->name('shifts.saveBatch');

        // ===== REKAP ABSENSI =====
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])
            ->name('absensi.rekap');

        // ===== POS CSV =====
        Route::get('/import/csv', [PosImportController::class, 'showUpload'])
            ->name('pos.import.form');

        Route::post('/api/pos/import', [PosImportController::class, 'upload']);
        Route::post('/pos/import', [PosImportController::class, 'import'])
            ->name('api.pos.import');

        // ===== POS VERIFICATION =====
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

    /*
    |--------------------------------------------------------------------------
    | PEGAWAI ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['pegawai'])->group(function () {

        // ===== ABSENSI UTAMA =====
        Route::get('/absensi', [AbsensiController::class, 'index'])
            ->name('absensi.index');

        // CHECK-IN (POST ONLY)
        Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])
            ->name('absensi.checkin');

        // 🚫 BLOCK GET CHECKOUT (ANTI 403 NYEBELIN)
        Route::get('/absensi/checkout', function () {
            return redirect()->route('dashboard')
                ->with('error', 'Akses tidak valid.');
        });

        // CHECK-OUT (POST ONLY)
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])
            ->name('absensi.checkout');

        // ===== ABSENSI PENGGANTI =====
        Route::get('/absensi/pengganti',
            [AbsensiController::class, 'showPenggantiForm']
        )->name('absensi.pengganti.form');

        Route::post('/absensi/pengganti/store',
            [AbsensiController::class, 'storePengganti']
        )->name('absensi.pengganti.store');

        // ===== HISTORY =====
        Route::get('/pegawai/history',
            [DashboardController::class, 'history']
        )->name('pegawai.history');
    });
});