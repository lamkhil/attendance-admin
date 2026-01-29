<?php

use App\Http\Controllers\ExportRekapController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendancePdfController;


Route::get('/attendance/pdf', [AttendancePdfController::class, 'export'])->name('attendance.pdf');
Route::get('export-rekap', ['exportRekapBulanan', ExportRekapController::class]);
