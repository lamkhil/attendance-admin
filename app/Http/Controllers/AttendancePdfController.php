<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AttendancePdfController extends Controller
{
    /**
     * Export absensi 1 user ke PDF
     * contoh:
     * /attendance/pdf?user_id=12&month=11&year=2025
     */
    public function export(Request $request)
    {
        $month  = $request->integer('month', now()->month);
        $year   = $request->integer('year', now()->year);
        $userId = $request->integer('user_id');

        abort_if(!$userId, 422);
        $user = User::findOrFail($userId);

        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return view('attendance_export', compact(
            'attendances',
            'user',
            'month',
            'year'
        ));
    }
}
