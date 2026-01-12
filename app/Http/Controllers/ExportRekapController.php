<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\RekapAbsensiBulananExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportRekapController extends Controller
{

    public function exportRekapBulanan(Request $request)
    {
        $month = (int) $request->month; // 1-12
        $year  = (int) $request->year;
        $position = (string) $request->posisiton;

        return Excel::download(
            new RekapAbsensiBulananExport($month, $year, $position),
            "rekap-absensi-{$month}-{$year}.xlsx"
        );
    }
}
