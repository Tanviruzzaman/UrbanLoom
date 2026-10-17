<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockVariants extends TableWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    /** Anything at or below this is considered low. */
    private const THRESHOLD = 5;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low stock')
            ->description('Sizes at or below '.self::THRESHOLD.' in stock — restock or deactivate them.')
            ->query(
                ProductVariant::query()
                    ->with('product:id,name,slug')
                    ->where('stock_quantity', '<=', self::THRESHOLD)
                    // A variant whose product was deleted is not actionable.
                    ->whereHas('product')
                    ->orderBy('stock_quantity')
                    ->orderBy('product_id')
            )
            ->recordUrl(fn (ProductVariant $record): ?string => $record->product
                ? ProductResource::getUrl('edit', ['record' => $record->product])
                : null)
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('label')
                    ->label('Size / colour')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->size('sm')
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Editable here so restocking never needs a page change.
                TextInputColumn::make('stock_quantity')
                    ->label('Stock')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (int) $state === 0 ? 'Out of stock' : 'Low')
                    ->color(fn ($state): string => (int) $state === 0 ? 'danger' : 'warning'),
            ])
            ->emptyStateHeading('Stock levels are healthy')
            ->emptyStateDescription('No size is down to '.self::THRESHOLD.' or fewer.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
