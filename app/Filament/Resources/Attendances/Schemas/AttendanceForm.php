<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->required(),
                DateTimePicker::make('check_in'),
                TextInput::make('check_in_lat')
                    ->numeric(),
                TextInput::make('check_in_lng')
                    ->numeric(),
                TextInput::make('check_in_photo'),
                DateTimePicker::make('check_out'),
                TextInput::make('check_out_lat')
                    ->numeric(),
                TextInput::make('check_out_lng')
                    ->numeric(),
                TextInput::make('check_out_photo'),
                TextInput::make('status')
                    ->required()
                    ->default('hadir'),
                TextInput::make('work_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('overtime_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
