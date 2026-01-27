<?php

namespace App\Filament\Qontak\Resources\TelegramGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TelegramGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('chat_id')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('link'),
                TextInput::make('bot_token'),
            ]);
    }
}
