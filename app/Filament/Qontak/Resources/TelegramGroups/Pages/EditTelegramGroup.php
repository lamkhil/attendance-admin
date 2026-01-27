<?php

namespace App\Filament\Qontak\Resources\TelegramGroups\Pages;

use App\Filament\Qontak\Resources\TelegramGroups\TelegramGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTelegramGroup extends EditRecord
{
    protected static string $resource = TelegramGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
