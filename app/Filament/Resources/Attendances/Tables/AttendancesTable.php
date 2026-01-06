<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_in_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in_lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in_photo')
                    ->searchable(),
                TextColumn::make('check_out')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_out_lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_out_photo')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('work_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
