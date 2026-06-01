<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PemasukanKasController;
use App\Http\Controllers\Api\PengeluaranKasController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GaleriKegiatanController;
use App\Http\Controllers\Api\PenjualanRongsokController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// PUBLIC
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);

// FORGOT PASSWORD
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-reset-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

// PROTECTED
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

Route::get('/storage-file/{path}', function ($path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})->where('path', '.*');

// ADMIN ONLY
Route::middleware([
    'auth:sanctum',
    'role:admin'
])->group(function () {
    Route::get('/admin', function () {
        return response()->json([
            'message' => 'Selamat datang admin'
        ]);
    });
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::patch('/users/{id}/role', [UserManagementController::class, 'updateRole']);
    Route::patch('/users/{id}/status', [UserManagementController::class, 'updateStatus']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
});

// PETUGAS ONLY
Route::middleware([
    'auth:sanctum',
    'role:petugas'
])->group(function () {

    Route::get('/petugas', function () {
        return response()->json([
            'message' => 'Selamat datang petugas'
        ]);
    });

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('/penjualan-rongsok', PenjualanRongsokController::class);
    Route::apiResource('/pemasukan-kas', PemasukanKasController::class);
    Route::apiResource('/pengeluaran-kas', PengeluaranKasController::class);
    Route::apiResource('/galeri-kegiatan', GaleriKegiatanController::class);
    Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan']);
    Route::get('/laporan/bulanan/export-excel', [LaporanController::class, 'exportExcel']);
    Route::get('/galeri-semua-bukti', [GaleriKegiatanController::class, 'semuaBukti']);
});

// USER ONLY
Route::middleware([
    'auth:sanctum',
    'role:user'
])->group(function () {

    Route::get('/user', function () {
        return response()->json([
            'message' => 'Selamat datang user'
        ]);
    });
});

// ADMIN DAN PETUGAS
Route::middleware([
    'auth:sanctum',
    'role:admin,petugas'
])->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ]);

    Route::get('/dashboard/statistik', [
        DashboardController::class,
        'statistik'
    ]);

    Route::apiResource(
        'pemasukan-kas',
        PemasukanKasController::class
    );

    Route::apiResource(
        'pengeluaran-kas',
        PengeluaranKasController::class
    );


    Route::apiResource(
        'galeri-kegiatan',
        GaleriKegiatanController::class
    );

    Route::apiResource(
        'penjualan-rongsok',
        PenjualanRongsokController::class
    );

    Route::get('/laporan/bulanan/export-excel', [LaporanController::class, 'exportExcel']);
    Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan']);
    Route::get('/galeri-semua-bukti', [GaleriKegiatanController::class, 'semuaBukti']);
});

// SEMUA ROLE
Route::get('/public/transparansi', [PublicController::class, 'transparansi']);
