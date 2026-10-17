<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductImage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->required()
                    ->helperText('JPG or PNG, up to 4 MB. A 4:5 portrait crop fits the storefront grid best.')
                    ->columnSpanFull(),

                Toggle::make('is_primary')
                    ->label('Primary image')
                    ->helperText('Shown on product cards. Only one image should be primary.'),

                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->integer()
                    ->minValue(0)
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_path')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->height(64),

                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),

                TextInputColumn::make('sort_order')
                    ->label('Order')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Upload image')
                    ->after(fn (ProductImage $record) => self::enforceSinglePrimary($record)),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn (ProductImage $record) => self::enforceSinglePrimary($record)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No images yet')
            ->emptyStateDescription('Upload an image — the storefront falls back to a placeholder until you do.');
    }

    /**
     * Keep exactly one primary image per product: marking one primary clears
     * the flag on its siblings.
     */
    private static function enforceSinglePrimary(ProductImage $record): void
    {
        if (! $record->is_primary) {
            return;
        }

        ProductImage::where('product_id', $record->product_id)
            ->whereKeyNot($record->getKey())
            ->update(['is_primary' => false]);
    }
}
