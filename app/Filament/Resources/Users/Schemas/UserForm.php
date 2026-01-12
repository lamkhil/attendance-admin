<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('profile_photo')
                    ->image()
                    ->avatar()
                    ->columnSpanFull(),
                TextInput::make('nik')
                    ->label('NIK')
                    ->maxLength(16)
                    ->minLength(16)
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Hidden::make('email_verified_at')
                    ->default(fn() => now()),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Select::make('role')
                    ->required()
                    ->live()
                    ->options([
                        'admin' => 'Administrator',
                        'user' => 'User',
                    ])
                    ->native(false),
                TextInput::make('phone')
                    ->tel(),
                DatePicker::make('date_of_birth'),
                Select::make('position')
                    ->options([
                        'Staff' => 'Staff',
                        'Magang' => 'Magang',
                    ])
                    ->hidden(function($get){
                        return $get('role') === 'admin';
                    })
                    ->native(false),
            ]);
    }
}
