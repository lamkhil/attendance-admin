<?php

namespace App\Filament\Qontak\Resources\TelegramChannels\Pages;

use App\Filament\Qontak\Resources\TelegramChannels\TelegramChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTelegramChannels extends ListRecords
{
    protected static string $resource = TelegramChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
