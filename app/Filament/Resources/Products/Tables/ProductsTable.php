<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                // Aggregate the variant stock in SQL rather than loading every
                // variant row just to sum it in PHP.
                fn (Builder $query) => $query
                    ->with(['category', 'primaryImage'])
                    ->withSum('variants as stock_total', 'stock_quantity')
            )
            ->columns([
                ImageColumn::make('primary_image_url')
                    ->label('')
                    ->getStateUsing(fn (Product $record): string => $record->primaryImageUrl())
                    ->square()
                    ->size(44),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record): ?string => $record->slug),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->placeholder('Uncategorised'),

                TextColumn::make('price')
                    ->formatStateUsing(fn ($state): string => bdt($state))
                    ->description(fn (Product $record): ?string => $record->on_sale
                        ? 'was '.bdt($record->compare_at_price)
                        : null)
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('stock_total')
                    ->label('Stock')
                    ->badge()
                    ->alignEnd()
                    ->sortable()
                    ->default(0)
                    ->formatStateUsing(fn ($state): string => number_format((int) $state))
                    ->color(fn ($state): string => match (true) {
                        (int) $state <= 0 => 'danger',
                        (int) $state <= 5 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('variants_count')
                    ->label('Sizes')
                    ->counts('variants')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...self::toggleBulkActions(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Add a product, then give it sizes and images.');
    }

    /**
     * Bulk activate / deactivate / feature / unfeature.
     *
     * Each uses a single UPDATE ... WHERE id IN (...) rather than saving
     * records one at a time.
     *
     * @return array<int, BulkAction>
     */
    private static function toggleBulkActions(): array
    {
        $toggles = [
            ['activate', 'Activate', 'is_active', true, 'heroicon-m-eye', 'success'],
            ['deactivate', 'Deactivate', 'is_active', false, 'heroicon-m-eye-slash', 'gray'],
            ['feature', 'Mark as featured', 'is_featured', true, 'heroicon-m-star', 'warning'],
            ['unfeature', 'Remove from featured', 'is_featured', false, 'heroicon-m-star', 'gray'],
        ];

        return array_map(
            fn (array $toggle) => BulkAction::make($toggle[0])
                ->label($toggle[1])
                ->icon($toggle[4])
                ->color($toggle[5])
                ->requiresConfirmation()
                ->action(function (Collection $records) use ($toggle) {
                    Product::whereKey($records->modelKeys())->update([$toggle[2] => $toggle[3]]);
                })
                ->deselectRecordsAfterCompletion(),
            $toggles,
        );
    }
}
