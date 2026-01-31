<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\TakonSobat\QontakController;
use App\Http\Controllers\TakonSobat\TelegramWebhookController;
use Illuminate\Support\Facades\Log;

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
    Route::post('/action2', [AttendanceController::class, 'action2']);

    Route::get('/summary', [AttendanceController::class, 'monthlySummary']);
});

Route::post('/upload', [UploadController::class, 'store'])->middleware('auth:sanctum');

Route::post('callback', [QontakController::class, 'callback']);
Route::post('room-interaction', [QontakController::class, 'roomInteraction']);

Route::post('webhook/telegram', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

Route::post('bot', [QontakController::class, 'botCallback']);

