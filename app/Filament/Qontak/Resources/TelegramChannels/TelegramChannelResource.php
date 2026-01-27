<?php

namespace App\Filament\Qontak\Resources\TelegramChannels;

use App\Filament\Qontak\Resources\TelegramChannels\Pages\CreateTelegramChannel;
use App\Filament\Qontak\Resources\TelegramChannels\Pages\EditTelegramChannel;
use App\Filament\Qontak\Resources\TelegramChannels\Pages\ListTelegramChannels;
use App\Filament\Qontak\Resources\TelegramChannels\Pages\ViewTelegramChannel;
use App\Filament\Qontak\Resources\TelegramChannels\Schemas\TelegramChannelForm;
use App\Filament\Qontak\Resources\TelegramChannels\Schemas\TelegramChannelInfolist;
use App\Filament\Qontak\Resources\TelegramChannels\Tables\TelegramChannelsTable;
use App\Models\TelegramChannel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TelegramChannelResource extends Resource
{
    protected static ?string $model = TelegramChannel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TelegramChannelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TelegramChannelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelegramChannelsTable::configure($table);
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
            'index' => ListTelegramChannels::route('/'),
            'create' => CreateTelegramChannel::route('/create'),
            'view' => ViewTelegramChannel::route('/{record}'),
            'edit' => EditTelegramChannel::route('/{record}/edit'),
        ];
    }
}
