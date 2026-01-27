<?php

namespace App\Filament\Qontak\Resources\TelegramChannels\Pages;

use App\Filament\Qontak\Resources\TelegramChannels\TelegramChannelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTelegramChannel extends ViewRecord
{
    protected static string $resource = TelegramChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
