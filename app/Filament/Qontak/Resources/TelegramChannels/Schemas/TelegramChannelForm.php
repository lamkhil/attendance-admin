<?php

namespace App\Filament\Qontak\Resources\TelegramChannels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TelegramChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('chat_id')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('link'),
            ]);
    }
}
