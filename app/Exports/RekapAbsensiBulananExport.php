<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapAbsensiBulananExport implements FromCollection, WithHeadings
{
    protected int $month;
    protected int $year;
    protected string $position;

    public function __construct(int $month, int $year, $position)
    {
        $this->month = $month;
        $this->year  = $year;
        $this->position = $position;
    }

    public function headings(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        $headers = ['Pegawai', 'Email'];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $headers[] = str_pad($d, 2, '0', STR_PAD_LEFT);
        }

        return $headers;
    }

    public function collection()
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        $users = User::where('role', 'user')->where('position', $this->position)->orderBy('name')->get();

        // Ambil semua absensi di bulan tsb sekali saja
        $attendances = Attendance::whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->get()
            ->groupBy(fn($a) => $a->user_id . '-' . $a->date);

        $rows = collect();

        foreach ($users as $user) {
            $row = [
                $user->name,
                $user->email,
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateKey = $user->id . '-' . $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);

                $attendance = $attendances->get($dateKey)?->first();

                if (! $attendance) {
                    $row[] = 'TM'; // Tidak Masuk
                } else {
                    $row[] = match ($attendance->status) {
                        'hadir'        => 'H',
                        'telat'        => 'TL',
                        'pulang cepat' => 'PC',
                        default        => 'H',
                    };
                }
            }

            $rows->push($row);
        }

        return $rows;
    }
}
