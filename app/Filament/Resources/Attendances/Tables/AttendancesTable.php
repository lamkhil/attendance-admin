<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('date')
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('date')
                    ->label('Tanggal')
                    ->orderQueryUsing(fn($query, string $direction) => $query->orderBy('date', 'desc')),
            ])
            ->columns([
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_in_lat')
                    ->sortable(),
                TextColumn::make('check_in_lng')
                    ->sortable(),
                ImageColumn::make('check_in_photo')
                    ->disk('s3')
                    ->action(function ($state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Check in Photo')
                            ->body(
                                '<img src="' . $state . '" class="max-w-[95vw] max-h-[95vh] object-contain mx-auto" />'
                            )
                            ->send();
                    }),
                TextColumn::make('check_out')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out_lat')
                    ->sortable(),
                TextColumn::make('check_out_lng')
                    ->sortable(),
                ImageColumn::make('check_out_photo')
                    ->disk('s3')
                    ->action(function ($state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Check Out Photo')
                            ->body(
                                '<img src="' . $state . '" class="max-w-[95vw] max-h-[95vh] object-contain mx-auto" />'
                            )
                            ->send();
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function($state){
                        return strtoupper($state);
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            'hadir'        => 'success',  // hijau
                            'telat'        => 'danger',  // kuning
                            'pulang cepat' => 'warning',   // merah
                            default        => 'gray',
                        };
                    }),
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
                Filter::make('date_range')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn($q, $date) => $q->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn($q, $date) => $q->whereDate('date', '<=', $date),
                            );
                    }),
            ], FiltersLayout::AboveContent)
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
