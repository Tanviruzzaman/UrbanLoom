<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),

                TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Leave blank to generate from the name. Changing it breaks existing links.')
                    ->placeholder('generated-from-name'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Inactive categories are hidden from the storefront navigation.')
                    ->default(true),
            ])
            ->columns(1);
    }
}
