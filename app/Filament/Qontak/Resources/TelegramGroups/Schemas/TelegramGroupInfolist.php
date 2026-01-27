<?php

namespace App\Filament\Qontak\Resources\TelegramGroups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TelegramGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('chat_id'),
                TextEntry::make('slug'),
                TextEntry::make('link')
                    ->placeholder('-'),
                TextEntry::make('bot_token'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
