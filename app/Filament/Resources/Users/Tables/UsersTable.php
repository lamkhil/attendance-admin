<?php

namespace App\Filament\Resources\Users\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('profile_photo')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                TextColumn::make('nik')
                    ->searchable(),
                TextColumn::make('position')
                    ->searchable(),
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
                Action::make('exportAbsensi')
                    ->label('Export Absensi')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->modalHeading('Export Absensi Pegawai')
                    ->modalSubmitActionLabel('Export PDF')
                    ->schema([
                        Select::make('month')
                            ->label('Bulan')
                            ->options(
                                collect(range(1, 12))->mapWithKeys(fn ($m) => [
                                    $m => Carbon::create()->month($m)->translatedFormat('F')
                                ])
                            )
                            ->default(now()->month)
                            ->required(),

                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $url = route('attendance.pdf', [
                            'user_id' => $record->id,
                            'month'   => $data['month'],
                            'year'    => $data['year'],
                        ]);

                        // buka di tab baru
                        return redirect()->away($url);
                    })
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
