<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('password')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('phone')
                ->rules(['max:50']),

            ImportColumn::make('date_of_birth')
                ->rules(['date']),

            ImportColumn::make('nik')
                ->rules(['max:50']),

            ImportColumn::make('position')
                ->rules(['max:100']),
        ];
    }

    /**
     * Cari user berdasarkan email (unique)
     */
    public function resolveRecord(): User
    {
        return User::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    /**
     * Manipulasi data sebelum disimpan
     */
    protected function beforeSave(): void
    {
        // Hash password kalau belum di-hash
        if (! empty($this->record->password)) {
            $this->record->password = Hash::make($this->record->password);
        }

        // Default role kalau kosong
        if (empty($this->record->role)) {
            $this->record->role = 'user';
        }

        // Default position kalau kosong
        if (empty($this->record->position)) {
            $this->record->position = 'Staff';
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' .
            Number::format($import->successful_rows) . ' ' .
            str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' .
                str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
