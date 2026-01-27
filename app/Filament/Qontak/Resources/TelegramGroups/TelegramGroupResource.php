<?php

namespace App\Filament\Qontak\Resources\TelegramGroups;

use App\Filament\Qontak\Resources\TelegramGroups\Pages\CreateTelegramGroup;
use App\Filament\Qontak\Resources\TelegramGroups\Pages\EditTelegramGroup;
use App\Filament\Qontak\Resources\TelegramGroups\Pages\ListTelegramGroups;
use App\Filament\Qontak\Resources\TelegramGroups\Pages\ViewTelegramGroup;
use App\Filament\Qontak\Resources\TelegramGroups\Schemas\TelegramGroupForm;
use App\Filament\Qontak\Resources\TelegramGroups\Schemas\TelegramGroupInfolist;
use App\Filament\Qontak\Resources\TelegramGroups\Tables\TelegramGroupsTable;
use App\Models\TelegramGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TelegramGroupResource extends Resource
{
    protected static ?string $model = TelegramGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TelegramGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TelegramGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelegramGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTelegramGroups::route('/'),
            'create' => CreateTelegramGroup::route('/create'),
            'view' => ViewTelegramGroup::route('/{record}'),
            'edit' => EditTelegramGroup::route('/{record}/edit'),
        ];
    }
}
