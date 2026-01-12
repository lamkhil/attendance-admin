<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-user-plus'),
            ImportAction::make()
                ->importer(UserImporter::class)
                ->label('Impor Pengguna'),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('staff')
                ->label('Staff')
                ->icon('heroicon-o-users')
                ->badge(
                    function(){
                        return User::where('position', 'Staff')
                        ->where('role','user')->count();
                    }
                )
                ->modifyQueryUsing(function ($query) {
                    return $query->where('position', 'Staff')
                        ->where('role','user');
                }),
            Tab::make('magang')
                ->label('Magang')
                ->icon('heroicon-o-users')
                ->badge(
                    function(){
                        return User::where('position', 'Magang')
                        ->where('role','user')->count();
                    }
                )
                ->modifyQueryUsing(function ($query) {
                    return $query->where('position', 'Magang')
                        ->where('role','user');
                }),
            Tab::make('admin')
                ->label('Administrator')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('role', 'admin');
                }),
        ];
    }
}
