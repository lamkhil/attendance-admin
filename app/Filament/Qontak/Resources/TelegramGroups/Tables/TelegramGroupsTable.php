<?php

namespace App\Filament\Qontak\Resources\TelegramGroups\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TelegramGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('chat_id')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('link')
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
                Action::make('sync')
                    ->label('Set Webhook')
                    ->action(function ($record) {
                        $telegramService = new \App\Services\TelegramService();
                        $webhookUrl = route('telegram.webhook');
                        $response = $telegramService->setWebHook($webhookUrl, $record->chat_id);
                        if ($response['ok']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Webhook set successfully.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to set webhook: ' . ($response['description'] ?? 'Unknown error'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !empty($record->bot_token))
                    ->color('secondary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
