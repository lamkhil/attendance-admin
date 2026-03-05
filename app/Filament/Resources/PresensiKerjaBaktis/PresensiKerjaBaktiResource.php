<?php

namespace App\Filament\Resources\PresensiKerjaBaktis;

use App\Filament\Resources\PresensiKerjaBaktis\Pages\CreatePresensiKerjaBakti;
use App\Filament\Resources\PresensiKerjaBaktis\Pages\EditPresensiKerjaBakti;
use App\Filament\Resources\PresensiKerjaBaktis\Pages\ListPresensiKerjaBaktis;
use App\Filament\Resources\PresensiKerjaBaktis\Pages\ViewPresensiKerjaBakti;
use App\Filament\Resources\PresensiKerjaBaktis\Schemas\PresensiKerjaBaktiForm;
use App\Filament\Resources\PresensiKerjaBaktis\Schemas\PresensiKerjaBaktiInfolist;
use App\Filament\Resources\PresensiKerjaBaktis\Tables\PresensiKerjaBaktisTable;
use App\Models\PresensiKerjaBakti;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PresensiKerjaBaktiResource extends Resource
{
    protected static ?string $model = PresensiKerjaBakti::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return PresensiKerjaBaktiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PresensiKerjaBaktiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresensiKerjaBaktisTable::configure($table);
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
            'index' => ListPresensiKerjaBaktis::route('/'),
            'create' => CreatePresensiKerjaBakti::route('/create'),
            'view' => ViewPresensiKerjaBakti::route('/{record}'),
            'edit' => EditPresensiKerjaBakti::route('/{record}/edit'),
        ];
    }
}
