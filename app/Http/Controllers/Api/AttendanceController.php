<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController
{
    public function index(Request $request)
    {
        return Attendance::where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function ($item) {
                $item->check_in_photo = $item->check_in_photo
                    ? Storage::disk('s3')->url($item->check_in_photo)
                    : null;

                $item->check_out_photo = $item->check_out_photo
                    ? Storage::disk('s3')->url($item->check_out_photo)
                    : null;

                return $item;
            });
    }


    public function action(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'photo' => 'nullable|string',
        ]);

        $user = $request->user();
        $today = now()->toDateString();

        $photo = $request->photo;

        if ($photo) {
            // Jika URL, ambil path setelah /storage/
            if (filter_var($photo, FILTER_VALIDATE_URL)) {

                $baseUrl = Storage::disk('s3')->url('');
                $photo = str_replace($baseUrl, '', $photo);
                $request->merge(['photo' => $photo]);
            }
        }

        return DB::transaction(function () use ($request, $user, $today) {

            // 1️⃣ Ambil / buat attendance summary
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'slug' => Str::uuid(),
                ]
            );

            // 2️⃣ Simpan log (tanpa type)
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'timestamp' => now(),
                'lat' => $request->lat,
                'lng' => $request->lng,
                'photo' => $request->photo,
                'device_info' => $request->userAgent(),
            ]);

            // 3️⃣ Ambil log pertama & terakhir hari ini (EFISIEN)
            $firstLog = AttendanceLog::where('attendance_id', $attendance->id)
                ->orderBy('timestamp')
                ->first();

            $lastLog = AttendanceLog::where('attendance_id', $attendance->id)
                ->orderByDesc('timestamp')
                ->first();

            // 4️⃣ Tentukan check-in & check-out
            $checkIn  = $firstLog?->timestamp;
            $checkOut = (
                $firstLog && $lastLog && $firstLog->id !== $lastLog->id
            ) ? $lastLog->timestamp : null;

            // 5️⃣ Hitung jam kerja (kalau < 2 log → 0)
            $workHours = 0;
            if ($checkIn && $checkOut) {
                $workHours = max(0, Carbon::parse($checkIn)->diffInHours($checkOut));
            }

            // 6️⃣ Tentukan status
            $status = 'izin';

            if ($checkIn && $schedule = $this->workSchedule(Carbon::parse($checkIn))) {

                $scheduleStart = \Carbon\Carbon::parse(
                    Carbon::parse($checkIn)->format('Y-M-d') . ' ' . $schedule['start']
                );

                $scheduleEnd = \Carbon\Carbon::parse(
                    Carbon::parse($checkIn)->format('Y-M-d') . ' ' . $schedule['end']
                );

                $isLate = Carbon::parse($checkIn)->gt($scheduleStart);
                $isEarlyLeave = false;

                if ($checkOut) {
                    $isEarlyLeave = Carbon::parse($checkOut)->lt($scheduleEnd);
                }

                if ($isLate) {
                    $status = 'telat';
                } elseif ($isEarlyLeave) {
                    $status = 'pulang cepat';
                } else {
                    $status = 'hadir';
                }
            }


            // 7️⃣ Update attendance summary
            $attendance->update([
                // check-in (log pertama)
                'check_in' => $checkIn,
                'check_in_lat' => $firstLog?->lat,
                'check_in_lng' => $firstLog?->lng,
                'check_in_photo' => $firstLog?->photo,

                // check-out (log terakhir)
                'check_out' => $checkOut,
                'check_out_lat' => $checkOut != null ? $lastLog?->lat : null,
                'check_out_lng' => $checkOut != null ? $lastLog?->lng : null,
                'check_out_photo' => $checkOut != null ? $lastLog?->photo : null,

                // summary
                'work_hours' => (int)$workHours,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'work_hours' => $workHours,
                'status' => $status,
            ]);
        });
    }

    public function action2(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'photo' => 'nullable|string',
            'datetime' => 'nullable|date',
        ]);

        $user = $request->user();

        // 🔑 DATETIME GLOBAL
        $actionTime = $request->filled('datetime')
            ? Carbon::parse($request->datetime)
            : now();

        $today = $actionTime->toDateString();

        // PHOTO NORMALIZATION
        $photo = $request->photo;
        if ($photo && filter_var($photo, FILTER_VALIDATE_URL)) {
            $baseUrl = Storage::disk('s3')->url('');
            $photo = str_replace($baseUrl, '', $photo);
        }

        return DB::transaction(function () use (
            $request,
            $user,
            $today,
            $actionTime,
            $photo
        ) {

            // 1️⃣ Attendance summary
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'slug' => Str::uuid(),
                ]
            );

            // 2️⃣ Attendance log (PAKAI datetime request)
            $log = AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'timestamp' => $actionTime,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'photo' => $photo,
                'device_info' => $request->userAgent(),
            ]);

            // 3️⃣ Ambil log pertama & terakhir
            $firstLog = AttendanceLog::where('attendance_id', $attendance->id)
                ->orderBy('timestamp')
                ->first();

            $lastLog = AttendanceLog::where('attendance_id', $attendance->id)
                ->orderByDesc('timestamp')
                ->first();

            // 4️⃣ Check-in & check-out
            $checkIn  = $firstLog?->timestamp;
            $checkOut = (
                $firstLog && $lastLog && $firstLog->id !== $lastLog->id
            ) ? $lastLog->timestamp : null;

            // 5️⃣ Work hours
            $workHours = 0;
            if ($checkIn && $checkOut) {
                $workHours = max(
                    0,
                    Carbon::parse($checkIn)->diffInHours($checkOut)
                );
            }

            // 6️⃣ Status
            $status = 'izin';

            if ($checkIn && $schedule = $this->workSchedule(Carbon::parse($checkIn))) {

                $scheduleStart = Carbon::parse(
                    Carbon::parse($checkIn)->format('Y-m-d') . ' ' . $schedule['start']
                );

                $scheduleEnd = Carbon::parse(
                    Carbon::parse($checkIn)->format('Y-m-d') . ' ' . $schedule['end']
                );

                $isLate = Carbon::parse($checkIn)->gt($scheduleStart);
                $isEarlyLeave = $checkOut
                    ? Carbon::parse($checkOut)->lt($scheduleEnd)
                    : false;

                if ($isLate) {
                    $status = 'telat';
                } elseif ($isEarlyLeave) {
                    $status = 'pulang cepat';
                } else {
                    $status = 'hadir';
                }
            }

            // 7️⃣ Update summary
            $attendance->update([
                'check_in' => $checkIn,
                'check_in_lat' => $firstLog?->lat,
                'check_in_lng' => $firstLog?->lng,
                'check_in_photo' => $firstLog?->photo,

                'check_out' => $checkOut,
                'check_out_lat' => $checkOut ? $lastLog?->lat : null,
                'check_out_lng' => $checkOut ? $lastLog?->lng : null,
                'check_out_photo' => $checkOut ? $lastLog?->photo : null,

                'work_hours' => (int) $workHours,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'datetime_used' => $actionTime->toDateTimeString(),
                'work_hours' => $workHours,
                'status' => $status,
            ]);
        });
    }



    private function workSchedule(Carbon $date): ?array
    {
        return match ($date->dayOfWeek) {
            Carbon::MONDAY,
            Carbon::TUESDAY,
            Carbon::WEDNESDAY,
            Carbon::THURSDAY => [
                'start' => '07:30',
                'end'   => '16:00',
            ],

            Carbon::FRIDAY => [
                'start' => '07:30',
                'end'   => '16:30',
            ],

            Carbon::SATURDAY => [
                'start' => '09:00',
                'end'   => '14:00',
            ],

            default => null, // Minggu
        };
    }

    public function monthlySummary(Request $request)
    {
        $user = $request->user();

        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $summary = [
            'on_time' => $attendances->where('status', 'hadir')->count(),
            'late' => $attendances->where('status', 'telat')->count(),
            'early_leave' => $attendances->where('status', 'pulang_cepat')->count(),
            'total' => $attendances->count(),
        ];

        return response()->json([
            'data' => $summary,
        ]);
    }
}
