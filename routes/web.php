<?php

use App\Http\Controllers\ExportRekapController;
use Illuminate\Support\Facades\Route;


Route::get('export-rekap', ['exportRekapBulanan', ExportRekapController::class]);
