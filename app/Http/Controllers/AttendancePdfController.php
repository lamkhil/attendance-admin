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

        abort_if(! $userId, 422, 'user_id wajib diisi');

        $user = User::find($userId);
        abort_if(! $user, 404, 'User tidak ditemukan');

        // 🔴 eager load logs (anti S3 flood)
        $attendances = Attendance::where('user_id', $user->id)
            ->with('user')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        abort_if($attendances->isEmpty(), 404, 'Data absensi tidak ditemukan');

        $pdf = Pdf::loadView('attendance_export', [
                'attendances' => $attendances,
                'user'        => $user,
                'month'       => $month,
                'year'        => $year,
            ])
            ->setPaper('A4', 'portrait')
            ->setOption('isRemoteEnabled', true);

        $filename = sprintf(
            'absensi-%s-%02d-%d.pdf',
            str($user->name)->slug(),
            $month,
            $year
        );

        return $pdf->stream($filename);
    }
}
