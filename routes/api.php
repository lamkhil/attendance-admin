<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UploadController;

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/verify-otp', [AuthController::class, 'verifyLoginOtp']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::get('/users', [AuthController::class, 'allUsers']);


Route::middleware('auth:sanctum')->prefix('attendance')->group(function () {

    // 🔹 Ambil data absensi (hari ini / histori)
    Route::get('/', [AttendanceController::class, 'index']);

    // 🔹 Action tap absensi (log terus, auto hitung)
    Route::post('/action', [AttendanceController::class, 'action']);

    Route::get('/summary', [AttendanceController::class, 'monthlySummary']);

});

Route::post('/upload', [UploadController::class, 'store'])->middleware('auth:sanctum');
