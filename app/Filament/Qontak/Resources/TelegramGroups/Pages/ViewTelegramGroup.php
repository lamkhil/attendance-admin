<?php

namespace App\Filament\Qontak\Resources\TelegramGroups\Pages;

use App\Filament\Qontak\Resources\TelegramGroups\TelegramGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTelegramGroup extends ViewRecord
{
    protected static string $resource = TelegramGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
