<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Exports\RekapAbsensiBulananExport;
use Maatwebsite\Excel\Facades\Excel;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportRekapBulanan')
                ->label('Export Rekap Bulanan')
                ->schema([
                    Select::make('month')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->native(false)
                        ->required(),
                    TextInput::make('year')
                        ->numeric()
                        ->default(now()->year)
                        ->required(),
                    Select::make('position')
                        ->options([
                            'Staff' => 'Staff',
                            'Magang' => 'Magang',
                        ])
                        ->hidden(function ($get) {
                            return $get('role') === 'admin';
                        })
                        ->native(false),
                ])
                ->action(function (array $data) {
                    return Excel::download(
                        new RekapAbsensiBulananExport(
                            (int) $data['month'],
                            (int) $data['year'],
                            (string) $data['position']
                        ),
                        'rekap-absensi.xlsx'
                    );
                }),
        ];
    }
}
