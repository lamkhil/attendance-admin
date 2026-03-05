<?php

namespace App\Filament\Resources\PresensiKerjaBaktis\Pages;

use App\Filament\Resources\PresensiKerjaBaktis\PresensiKerjaBaktiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPresensiKerjaBaktis extends ListRecords
{
    protected static string $resource = PresensiKerjaBaktiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
