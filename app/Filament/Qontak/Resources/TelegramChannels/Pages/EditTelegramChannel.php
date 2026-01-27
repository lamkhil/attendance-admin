<?php

namespace App\Filament\Qontak\Resources\TelegramChannels\Pages;

use App\Filament\Qontak\Resources\TelegramChannels\TelegramChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTelegramChannel extends EditRecord
{
    protected static string $resource = TelegramChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
