<?php

namespace App\Filament\Resources\PresensiKerjaBaktis\Pages;

use App\Filament\Resources\PresensiKerjaBaktis\PresensiKerjaBaktiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPresensiKerjaBakti extends ViewRecord
{
    protected static string $resource = PresensiKerjaBaktiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
