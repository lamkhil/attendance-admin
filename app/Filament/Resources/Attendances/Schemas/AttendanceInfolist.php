<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('slug'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('check_in')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('check_in_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_in_lng')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_in_photo')
                    ->placeholder('-'),
                TextEntry::make('check_out')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('check_out_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_out_lng')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_out_photo')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('work_hours')
                    ->numeric(),
                TextEntry::make('overtime_hours')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
