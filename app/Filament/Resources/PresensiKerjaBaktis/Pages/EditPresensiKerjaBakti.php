<?php

namespace App\Filament\Resources\PresensiKerjaBaktis\Pages;

use App\Filament\Resources\PresensiKerjaBaktis\PresensiKerjaBaktiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPresensiKerjaBakti extends EditRecord
{
    protected static string $resource = PresensiKerjaBaktiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
