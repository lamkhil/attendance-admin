<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Umum')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('user_id')
                            ->label('Pegawai')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),

                        Select::make('status')
                            ->options([
                                'hadir' => 'Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'alpha' => 'Alpha',
                            ])
                            ->default('hadir')
                            ->required(),
                    ]),

                Section::make('Check In')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('check_in')
                            ->label('Waktu Check In'),

                        FileUpload::make('check_in_photo')
                            ->label('Foto Check In')
                            ->disk('s3')
                            ->getUploadedFileNameForStorageUsing(
                                fn($state) =>
                                str_replace(
                                    Storage::disk('s3')->url(''),
                                    '',
                                    $state
                                )
                            ),

                        TextInput::make('check_in_lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.0000001),

                        TextInput::make('check_in_lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.0000001),
                    ]),

                Section::make('Check Out')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('check_out')
                            ->label('Waktu Check Out'),

                        FileUpload::make('check_out_photo')
                            ->disk('s3')
                            ->getUploadedFileNameForStorageUsing(
                                fn($state) =>
                                str_replace(
                                    Storage::disk('s3')->url(''),
                                    '',
                                    $state
                                )
                            ),

                        TextInput::make('check_out_lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.0000001),

                        TextInput::make('check_out_lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.0000001),
                    ]),

                Section::make('Perhitungan Jam')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('work_hours')
                            ->label('Jam Kerja')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('overtime_hours')
                            ->label('Jam Lembur')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                    ]),
            ]);
    }
}
