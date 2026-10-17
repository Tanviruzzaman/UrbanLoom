<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Leave blank to generate from the name. Changing it breaks existing links.')
                            ->placeholder('generated-from-name')
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                                Toggle::make('is_active')->label('Active')->default(true),
                            ]),

                        Textarea::make('description')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->description('Set a compare-at price higher than the price to show a sale badge.')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->prefix('৳'),

                        TextInput::make('compare_at_price')
                            ->label('Compare-at price')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->prefix('৳')
                            ->helperText('Shown struck through. Leave blank if not on sale.')
                            ->gt('price')
                            ->validationMessages([
                                'gt' => 'The compare-at price must be higher than the price, or left blank.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Visibility')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive products are hidden from the storefront.')
                            ->default(true),

                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Featured products appear on the home page.'),
                    ])
                    ->columns(2),
            ]);
    }
}
