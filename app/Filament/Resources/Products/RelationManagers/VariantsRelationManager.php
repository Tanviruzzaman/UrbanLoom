<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Sizes & stock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Optional and free-text: jewellery has no size, and a bangle
                // or ring may use its own sizing that isn't S/M/L.
                Select::make('size')
                    ->options(array_combine(
                        ['One Size', 'XS', 'S', 'M', 'L', 'XL', 'XXL'],
                        ['One Size', 'XS', 'S', 'M', 'L', 'XL', 'XXL'],
                    ))
                    ->allowHtml(false)
                    ->searchable()
                    ->native(false)
                    ->helperText('Leave blank for jewellery and anything unsized.'),

                TextInput::make('color')
                    ->label('Colour')
                    ->maxLength(255)
                    ->placeholder('Black'),

                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Optional, but must be unique if set.'),

                TextInput::make('stock_quantity')
                    ->label('Stock')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size')
            ->columns([
                TextColumn::make('size')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Unsized'),

                TextColumn::make('color')
                    ->label('Colour')
                    ->placeholder('—'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->size('sm')
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                // Editable inline: restocking is the most common admin task.
                TextInputColumn::make('stock_quantity')
                    ->label('Stock')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->headerActions([
                CreateAction::make()->label('Add size'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No sizes yet')
            ->emptyStateDescription('Add at least one size so customers can buy this product.');
    }
}
